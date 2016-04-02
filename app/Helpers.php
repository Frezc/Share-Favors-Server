<?php
use App\User;
use App\TagRepo;
use App\TagLink;
use App\Tag;
use App\RecentItem;
use App\TagItem;
use App\Item;
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
    $tagItems = TagItem::whereIn('itemid', deMul($itemsId))->get();
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

//传入仓库列表object 返回[recentItems, items]
function getRecentItems($itemList, $showAll) {
    if(empty($itemList)) {
        return $itemList = [];
    }
    $itemsId = array();
    foreach($itemList as $item) {
        $itemsId[] = $item->id;
    }
    //显示部分
    if($showAll == 0) {
    $recentItemsList = Item::whereIn('repo_id', deMul($itemsId))->where('status', 1)->orderBy('updated_at', 'DESC')->take(10)->get();
    }
    //显示全部
    if($showAll == 1) {
    $recentItemsList = Item::whereIn('repo_id', deMul($itemsId))->orderBy('updated_at', 'DESC')->take(10)->get();   
    }
    addTagsToItems($recentItemsList);
    $getRecentRepos = array();
    $getRecentItems = array();
    foreach($recentItemsList as $recentItems) {
        $thisId = $recentItems->repo_id;
        if($recentItems->type == 0) {
            unset($recentItems->url);
            unset($recentItems->type);
            unset($recentItems->repo_id);
            $getRecentItems[ $thisId ][] = ['repository' => $recentItems, 'type' => 0 ];
        }
        if($recentItems->type ==1 ) {
            unset($recentItems->repo_id, 
                   $recentItems->type, 
                   $recentItems->creator_id,
                   $recentItems->creator_name,
                   $recentItems->status,
                   $recentItems->link_num,
                   $recentItems->repo_num
                   );
            //unset($recentItems->type);
            //unset($recentItems->)
            $getRecentItems[ $thisId ][] = ['links' => $recentItems, 'type' => 1];
        }
    }
    $setRecentItemsById = array();
    $return = array();
    foreach($itemList as $item) {
        $return[] = [ 
            'repository' => $item,
            'recentItems' => isset($getRecentItems[ $item->id ])?$getRecentItems[ $item->id] : [], 
            ];
    }
    return $return;
}

function formatObject($items) {
    foreach($items as $item) {
        if($item->type == 0) {
            unset($item->url);
            unset($item->type);
            unset($item->repo_id);
        }
        if($item->type == 1) {
           unset(
            $item->repo_id, 
            $item->type, 
            $item->creator_id,
            $item->creator_name,
            $item->status,
            $item->link_num,
            $item->repo_num
            ); 
        }
    }
}

function formatAnObject($item) {
    if($item->type == 0) {
        unset($item->url);
        unset($item->type);
        unset($item->repo_id);
    }
    if($item->type == 1) {
        unset(
        $item->repo_id, 
        $item->type, 
        $item->creator_id,
        $item->creator_name,
        $item->status,
        $item->link_num,
        $item->repo_num
        ); 
    }

}