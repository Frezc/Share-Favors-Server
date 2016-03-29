<?php
use App\User;
use App\TagRepo;
use App\TagLink;
use App\Tag;
use App\RecentItem;
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
    //输入对象
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
//输入对象
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
        $item['tags'] = isset($TagRepoById[$item->id]) ? $TagRepoById[$item->id] : [] ;
    }
}
//link批量加tag
//输入对象
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


//输入对象
//重构集合为item
function addTagsToItems($itemList) {
    $itemsId = array();
    foreach($itemList as $item) {
        $itemsId[] = $item->id;
    }
    $tagItems = TagItem::whereIn('id', deMul($itemsId))->get();
    $tagsId = array();
    foreach($tagItems as $tagItem) {
        $tagsId[] = $tagItem->tagid;
    }
    $tags = Tag::whereIn('id', deMul($tagsId))->get();
    $GetTag = array();
    foreach($tags as $tag) {
        $GetTag[$tag->id] = $tag->toArray();
    }
    $TagItemById = array();
    foreach($tagItems as $tagItem) {
        $TagItemById[$tagItem->itemid][] = $GetTag[$tagItem->tagid];
    }
    foreach($itemList as $item) {
        $item['tags'] = isset($TagItemById[$item->id]) ? $TagItemById[$item->id] :null ;
    } 
}

function getRecentItems($itemList) {
    $itemsId = array();
    foreach($itemList as $item) {
        $itemsId[] = $item->id;
    }
    $recentItemsList = RecentItem::whereIn('repoid', deMul($itemsId))->orderBy('created_at', 'DESC')->get();
    foreach($recentItemsList as $recentItems) {
        
    }
}