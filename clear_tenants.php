<?php

$tenants = \App\Models\User::where('role', 'tenant')->get();
foreach($tenants as $t) {
    if(class_exists('\App\Models\MikrotikRouter')) \App\Models\MikrotikRouter::where('user_id', $t->id)->delete();
    if(class_exists('\App\Models\SubscriptionTransaction')) \App\Models\SubscriptionTransaction::where('user_id', $t->id)->delete();
    if(class_exists('\App\Models\Customer')) \App\Models\Customer::where('user_id', $t->id)->delete();
    if(class_exists('\App\Models\Customer')) \App\Models\Customer::where('tenant_id', $t->id)->delete();
    if(class_exists('\App\Models\NetworkNode')) \App\Models\NetworkNode::where('user_id', $t->id)->delete();
    if(class_exists('\App\Models\Ticket')) \App\Models\Ticket::where('tenant_id', $t->id)->delete();
    if(class_exists('\App\Models\Transaction')) \App\Models\Transaction::where('tenant_id', $t->id)->delete();
    $t->delete();
}
echo "All tenant data deleted successfully.\n";
