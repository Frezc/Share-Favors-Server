<?php
use App\User;
function setCreator($item) {
    $item->creatorId = $item->creator;
    unset($item->creator);
    $item->creatorName = User::findorFail($item->creatorId)->nickname;
}