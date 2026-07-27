<?php
$idsToKeep = App\Models\Client::take(4)->pluck("id")->toArray();
$fc = App\Models\Client::where("email", "like", "%freecursor%")->first();
if($fc) {
    $idsToKeep[] = $fc->id;
}
App\Models\Client::whereNotIn("id", $idsToKeep)->delete();
echo "Clients cleaned\n";
