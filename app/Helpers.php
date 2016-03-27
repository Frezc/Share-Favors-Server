<?php
use App\User;
use App\TagRepo;
use App\TagLink;
use App\Tag;
function setCreator($item) {
    $item->creatorId = $item->creator;
    unset($item->creator);
    $item->creatorName = User::findorFail($item->creatorId)->nickname;
    
}

function setCreatorName($item, $nickname) {
    $item->creatorId = $item->creator;
    unset($item->creator);
    $item->creatorName = $nickname;
    
}
//添加作者信息
function searchCreator($items) {
    //输入数组
    $itemId= array();
    $filp = array();
    $idSeach = array();
    foreach($items as $item ) {
        $itemId[] = $item['creator'] ;
    }
    $flip = array_flip($itemId);
    $idSeach = array_flip($flip);
    $creatorNames = [];
    foreach(User::whereIn('id', $idSeach)->get() as $creator) {
        $creatorNames[$creator->id] = $creator->nickname;
    }
    foreach($items as &$item) {
        $item['creatorName'] = isset($creatorNames[$item['creator']])?$creatorNames[$item['creator']]:null;
        $item['creatorId'] = $item['creator'];
        unset($item['creator']);
    }
    return $items;
}

function searchCreatorFromObject($items) {
    //输入数组
    $itemId= array();
    $filp = array();
    $idSeach = array();
    foreach($items as $item ) {
        $itemId[] = $item->creator;
    }
    $flip = array_flip($itemId);
    $idSeach = array_flip($flip);
    $creatorNames = [];
    foreach(User::whereIn('id', $idSeach)->get() as $creator) {
        $creatorNames[$creator->id] = $creator->nickname;
    }
    foreach($items as &$item) {
        $item['creatorName'] = isset($creatorNames[$item['creator']])?$creatorNames[$item['creator']]:null;
        $item['creatorId'] = $item['creator'];
        unset($item['creator']);
    }
    return $items;
}
//去重
function deMul($item) {
    $flip = array();
    $return = array();
    $flip = array_flip($item);
    $return = array_flip($flip);
    return $return;
}
//仓库批量加tag
function addTagsToRepo($itemList) {
    $itemsId = array();
    foreach($itemList as $item) {
        $itemsId[] = $item->id;
    }
    $tagRepos = TagRepo::whereIn('repoid', deMul($itemsId))->get();
    $tagsId = array();
    foreach($tagRepos as $tagRepo) {
        $tagsId[] = $tagRepo->tagid;
    }
    $tags = Tag::whereIn('id', deMul($tagsId))->get();
    //dd($tags->toArray());
    $GetTag = array();
    foreach($tags as $tag) {
        $GetTag[$tag->id] = $tag->toArray();
    }
    //dd($GetTag);
    $TagRepoById = array();
    foreach($tagRepos as $tagRepo) {
        $TagRepoById[$tagRepo->repoid][] = $GetTag[$tagRepo->tagid];
    }
    //dd($itemList->toArray());
    //dd($TagRepoById);
    foreach($itemList as $item) {
        $item['tags'] = isset($TagRepoById[$item->id]) ? $TagRepoById[$item->id] : null ;
    }
}
//link批量加tag
function addTagsToLink($itemList) {
    $itemsId = array();
    foreach($itemList as $item) {
        $itemsId[] = $item->id;
    }
    $tagLinks = TagLink::whereIn('linkid', deMul($itemsId))->get();
    $tagsId = array();
    foreach($tagLinks as $tagLink) {
        $tagsId[] = $tagLink->tagid;
    }
    $tags = Tag::whereIn('id', deMul($tagsId))->get();
    $GetTag = array();
    foreach($tags as $tag) {
        $GetTag[$tag->id] = $tag->toArray();
    }
    $TagLinkById = array();
    foreach($tagLinks as $tagLink) {
        $TagLinkById[$tagLink->linkid][] = $GetTag[$tagLink->tagid];
    }
    foreach($itemList as $item) {
        $item['tags'] = isset($TagLinkById[$item->id]) ? $TagLinkById[$item->id] :null ;
    } 
}