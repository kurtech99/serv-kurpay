<?php

namespace App\Services;

use Exception;
use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected $client;
    protected $userId = null;

    public static function fetchSoftwareId($host, $port, $username, $password)
    {
        try {
            $client = new Client([
                'host' => $host,
                'user' => $username,
                'pass' => $password,
                'port' => (int) $port,
                'timeout' => 5 // 5 seconds timeout
            ]);

            $query = new Query('/system/license/print');
            $response = $client->query($query)->read();

            if (is_array($response) && isset($response[0]['software-id'])) {
                return $response[0]['software-id'];
            }
            
            // Fallback for some models
            $query = new Query('/system/routerboard/print');
            $response = $client->query($query)->read();
            if (is_array($response) && isset($response[0]['serial-number'])) {
                return $response[0]['serial-number'];
            }

            return null;
        } catch (Exception $e) {
            \Log::error("Failed fetching software-id: " . $e->getMessage());
            return null; // Connection failed or unsupported
        }
    }

    public function __construct()
    {
        // Connection is now established on demand to support multi-tenancy correctly
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    private function connect()
    {
        if ($this->client) return true;

        $userId = $this->userId ?? auth()->id();
        if (!$userId) return false;

        try {
            $user = \App\Models\User::find($userId);
            if (!$user) return false;
            
            $router = $user->routers()->where('is_primary', true)->first() 
                        ?? $user->routers()->first();

            if (!$router) {
                return false;
            }

            $this->client = new Client([
                'host' => $router->host,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => (int) $router->port,
            ]);
            return true;
        } catch (Exception $e) {
            \Log::error("MikroTik Connection Failed: " . $e->getMessage());
            return false;
        }
    }

    public function getClient()
    {
        $this->connect();
        return $this->client;
    }

    public function getProfiles()
    {
        if (!$this->connect()) return [];
        try {
            $query = new Query('/ip/hotspot/user/profile/print');
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            \Log::error("Failed fetching profiles: " . $e->getMessage());
            return [];
        }
    }

    public function addHotspotUser($username, $password, $profile, $uptimeLimit = null, $bytesLimit = null)
    {
        if (!$this->connect()) {
            throw new Exception("MikroTik client is not connected.");
        }

        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('profile', $profile);

        if ($uptimeLimit) {
            $query->equal('limit-uptime', $uptimeLimit);
        }

        if ($bytesLimit) {
            $query->equal('limit-bytes-total', $bytesLimit);
        }

        $response = $this->client->query($query)->read();

        // Check if there's an error in response
        if (isset($response['!trap'])) {
            throw new Exception("Failed to add user: " . json_encode($response['!trap']));
        }

        return true;
    }

    public function generateUniqueUsername($prefix = 'vp')
    {
        // Generate a random username, e.g. vp_5a2b
        return $prefix . '_' . substr(md5(uniqid(rand(), true)), 0, 5);
    }

    public function getLogs($limit = 5)
    {
        if (!$this->connect()) return [];
        try {
            $query = new Query('/log/print');
            $logs = $this->client->query($query)->read();
            
            if (is_array($logs)) {
                // Return latest logs
                return array_slice(array_reverse($logs), 0, $limit);
            }
            return [];
        } catch (\Exception $e) {
            \Log::error("Failed fetching logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * VPN Automation Methods
     */
    public static function createVpnAccount($server, $username, $password, $remoteIp, $profile = 'L2TP CLIENT', $isTrial = false)
    {
        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
            ]);

            // Check if secret already exists
            $findQuery = (new Query('/ppp/secret/print'))->where('name', $username);
            $secrets = $client->query($findQuery)->read();

            if (!empty($secrets)) {
                $setQuery = (new Query('/ppp/secret/set'))
                    ->equal('.id', $secrets[0]['.id'])
                    ->equal('password', $password)
                    ->equal('profile', $profile)
                    ->equal('remote-address', $remoteIp)
                    ->equal('disabled', 'no')
                    ->equal('comment', $isTrial ? 'free_trial' : 'paid_account');
                $client->query($setQuery)->read();
                return true;
            }

            // Create PPP Secret
            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('profile', $profile)
                ->equal('local-address', '26.8.99.1')
                ->equal('remote-address', $remoteIp)
                ->equal('service', 'any')
                ->equal('comment', $isTrial ? 'free_trial' : 'paid_account');
            $response = $client->query($query)->read();
            
            if (isset($response['!trap'])) {
                throw new Exception("API Error: " . json_encode($response['!trap']));
            }

            return true;
        } catch (Exception $e) {
            \Log::error("VPN Account Creation Failed: " . $e->getMessage());
            return false;
        }
    }

    public static function deleteVpnAccount($server, $username)
    {
        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
            ]);

            $findQuery = (new Query('/ppp/secret/print'))
                ->where('name', $username);
            $secrets = $client->query($findQuery)->read();

            if (!empty($secrets)) {
                $removeQuery = (new Query('/ppp/secret/remove'))
                    ->equal('.id', $secrets[0]['.id']);
                $client->query($removeQuery)->read();
            }

            // Also disconnect active connection if exists
            $findActive = (new Query('/ppp/active/print'))
                ->where('name', $username);
            $active = $client->query($findActive)->read();
            
            if (!empty($active)) {
                $removeActive = (new Query('/ppp/active/remove'))
                    ->equal('.id', $active[0]['.id']);
                $client->query($removeActive)->read();
            }
            return true;
        } catch (Exception $e) {
            \Log::error("VPN Account Deletion Failed: " . $e->getMessage());
            return false;
        }
    }

    public static function disableVpnAccount($server, $username)
    {
        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
            ]);

            $findQuery = (new Query('/ppp/secret/print'))->where('name', $username);
            $secrets = $client->query($findQuery)->read();

            if (!empty($secrets)) {
                $disableQuery = (new Query('/ppp/secret/disable'))
                    ->equal('.id', $secrets[0]['.id']);
                $client->query($disableQuery)->read();
            }

            // Disconnect active connection
            $findActive = (new Query('/ppp/active/print'))->where('name', $username);
            $active = $client->query($findActive)->read();
            
            if (!empty($active)) {
                $removeActive = (new Query('/ppp/active/remove'))->equal('.id', $active[0]['.id']);
                $client->query($removeActive)->read();
            }
            return true;
        } catch (Exception $e) {
            \Log::error("VPN Account Disable Failed: " . $e->getMessage());
            return false;
        }
    }

    public static function enableVpnAccount($server, $username)
    {
        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
            ]);

            $findQuery = (new Query('/ppp/secret/print'))->where('name', $username);
            $secrets = $client->query($findQuery)->read();

            if (!empty($secrets)) {
                $enableQuery = (new Query('/ppp/secret/enable'))
                    ->equal('.id', $secrets[0]['.id']);
                $client->query($enableQuery)->read();
            }
            return true;
        } catch (Exception $e) {
            \Log::error("VPN Account Enable Failed: " . $e->getMessage());
            return false;
        }
    }

    public static function getAccountStatus($server, $username, $remoteIp)
    {
        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
            ]);

            $status = [
                'is_active' => false,
                'ping_ms' => null,
                'private_address' => null,
                'uptime' => null,
                'downtime' => null,
                'hardware' => null,
                'software' => null,
            ];

            // 1. Get Secret to extract local-address
            $secretQuery = (new Query('/ppp/secret/print'))->where('name', $username);
            $secret = $client->query($secretQuery)->read();
            
            $localAddress = 'Unknown';
            if (!empty($secret)) {
                $localAddress = $secret[0]['local-address'] ?? 'Unknown';
            }

            // 2. Default Hardware/Software to Unknown
            $status['hardware'] = 'MikroTik | Client Router';
            $status['software'] = 'RouterOS | Unknown Version';

            // 3. Try to get actual Hardware/Software from Neighbor Discovery
            $neighborQuery = new Query('/ip/neighbor/print');
            $neighbors = $client->query($neighborQuery)->read();
            if (!empty($neighbors)) {
                foreach ($neighbors as $n) {
                    $hasAddressMatch = isset($n['address']) && $n['address'] == $remoteIp;
                    $hasInterfaceMatch = isset($n['interface']) && str_contains(strtolower($n['interface']), strtolower($username));
                    
                    if ($hasAddressMatch || $hasInterfaceMatch) {
                        $board = $n['board'] ?? $n['board-name'] ?? null;
                        $version = $n['version'] ?? null;
                        
                        if ($board) $status['hardware'] = 'MikroTik | ' . $board;
                        if ($version) $status['software'] = 'RouterOS | ' . $version;
                        
                        break;
                    }
                }
            }

            // 3. Get PPP Active (Uptime & Address)
            $activeQuery = (new Query('/ppp/active/print'))->where('name', $username);
            $active = $client->query($activeQuery)->read();

            if (!empty($active)) {
                $status['is_active'] = true;
                $status['uptime'] = $active[0]['uptime'] ?? null;
                $status['private_address'] = "{$localAddress} <=> {$remoteIp}";
                
                // 4. Ping the active IP
                $pingQuery = (new Query('/ping'))
                    ->equal('address', $remoteIp)
                    ->equal('count', 3);
                $pingRes = $client->query($pingQuery)->read();
                
                $totalMs = 0;
                $replies = 0;
                foreach ($pingRes as $p) {
                    if (isset($p['time'])) {
                        $time = str_replace('ms', '', $p['time']);
                        $totalMs += (int)$time;
                        $replies++;
                    }
                }
                if ($replies > 0) {
                    $status['ping_ms'] = round($totalMs / $replies) . 'ms';
                } else {
                    $status['ping_ms'] = 'Timeout';
                }
            } else {
                // Not active, get last logged out from secret
                if (!empty($secret)) {
                    $lastOut = $secret[0]['last-logged-out'] ?? 'N/A';
                    $reason = $secret[0]['last-disconnect-reason'] ?? 'Unknown';
                    $status['downtime'] = "{$lastOut} | {$reason}";
                }
            }

            return $status;
        } catch (Exception $e) {
            \Log::error("VPN Status Check Failed: " . $e->getMessage());
            return null;
        }
    }

    public static function createVpnNat($server, $publicPort, $remoteIp, $localPort, $username = '', $protocol = 'tcp')
    {
        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
            ]);

            $findQuery = (new Query('/ip/firewall/nat/print'))
                ->where('dst-port', (string) $publicPort)
                ->where('action', 'dst-nat');
            $existingNat = $client->query($findQuery)->read();

            if (!empty($existingNat)) {
                $updateQuery = (new Query('/ip/firewall/nat/set'))
                    ->equal('.id', $existingNat[0]['.id'])
                    ->equal('to-addresses', $remoteIp)
                    ->equal('to-ports', (string)$localPort)
                    ->equal('comment', "vpn_{$username}{$localPort}");
                $client->query($updateQuery)->read();
                return true;
            }

            $query = (new Query('/ip/firewall/nat/add'))
                ->equal('chain', 'dstnat')
                ->equal('dst-address', $server->host)
                ->equal('protocol', $protocol)
                ->equal('dst-port', (string)$publicPort)
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp)
                ->equal('to-ports', (string)$localPort)
                ->equal('comment', "vpn_{$username}{$localPort}");

            $response = $client->query($query)->read();
            
            if (isset($response['!trap'])) {
                throw new Exception("API Error: " . json_encode($response['!trap']));
            }

            return true;
        } catch (Exception $e) {
            \Log::error("VPN NAT Creation Failed: " . $e->getMessage());
            return false;
        }
    }

    public static function deleteVpnNat($server, $publicPort)
    {
        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
            ]);

            $findQuery = (new Query('/ip/firewall/nat/print'))
                ->where('dst-port', (string) $publicPort)
                ->where('action', 'dst-nat');
            $natRules = $client->query($findQuery)->read();

            if (!empty($natRules)) {
                $removeQuery = (new Query('/ip/firewall/nat/remove'))
                    ->equal('.id', $natRules[0]['.id']);
                $client->query($removeQuery)->read();
            }
            return true;
        } catch (Exception $e) {
            \Log::error("VPN NAT Deletion Failed: " . $e->getMessage());
            return false;
        }
    }

    public static function updateVpnNat($server, $publicPort, $newLocalPort, $username = '')
    {
        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
            ]);

            // 1. Cari ID rule NAT berdasarkan dst-port
            $findQuery = (new Query('/ip/firewall/nat/print'))
                ->where('dst-port', (string) $publicPort)
                ->where('action', 'dst-nat');
            $natRules = $client->query($findQuery)->read();

            if (empty($natRules)) {
                return false;
            }

            $natId = $natRules[0]['.id'];

            // 2. Update to-ports dan comment
            $updateQuery = (new Query('/ip/firewall/nat/set'))
                ->equal('.id', $natId)
                ->equal('to-ports', $newLocalPort)
                ->equal('comment', "vpn_{$username}{$newLocalPort}");
            $response = $client->query($updateQuery)->read();
            
            if (isset($response['!trap'])) {
                throw new Exception("API Error: " . json_encode($response['!trap']));
            }

            return true;
        } catch (Exception $e) {
            \Log::error("VPN NAT Update Failed: " . $e->getMessage());
            return false;
        }
    }

    public static function getServerStats($server)
    {
        $start = microtime(true);
        $pingMs = '-';
        $status = 'offline';

        // Check TCP latency to API port
        $fp = @fsockopen($server->host, $server->port, $errno, $errstr, 2); // 2s timeout
        if ($fp) {
            $pingMs = round((microtime(true) - $start) * 1000) . ' ms';
            fclose($fp);
            $status = 'online';
        } else {
            return ['status' => 'offline'];
        }

        try {
            $client = new Client([
                'host' => $server->host,
                'user' => $server->username,
                'pass' => $server->password,
                'port' => (int) $server->port,
                'timeout' => 3
            ]);

            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();

            if (is_array($response) && isset($response[0])) {
                $data = $response[0];
                $cpu = $data['cpu-load'] ?? 0;
                $freeMem = $data['free-memory'] ?? 0;
                $totalMem = $data['total-memory'] ?? 1;

                $memPercent = round((($totalMem - $freeMem) / $totalMem) * 100);
                
                return [
                    'status' => 'online',
                    'ping' => $pingMs,
                    'cpu' => $cpu,
                    'memory' => $memPercent,
                    'uptime' => $data['uptime'] ?? '-'
                ];
            }
            
            return ['status' => 'online', 'ping' => $pingMs, 'cpu' => '-', 'memory' => '-', 'uptime' => '-'];
        } catch (Exception $e) {
            \Log::error("Failed fetching VPN Server stats: " . $e->getMessage());
            return ['status' => 'online', 'ping' => $pingMs, 'cpu' => '-', 'memory' => '-', 'uptime' => '-'];
        }
    }
}

