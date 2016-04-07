<?php
use App\User;
use App\TagRepo;
use App\TagLink;
use App\Tag;
use App\TagItem;
use App\Repolist;
use App\Link;
use App\Repository;
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
function addTagsToRepo($itemList) {
    $itemsId = array();
    foreach($itemList as $item) {
        $itemsId[] = $item->id;
    }
    $tagRepos = TagItem::whereIn('item_id', deMul($itemsId))->where('tagitem_type', 'App\Repository')->get();
    $tagsId = array();
    foreach($tagRepos as $tagRepo) {
        $tagsId[] = $tagRepo->tag_id;
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
        $TagRepoById[$tagRepo->item_id][] = $GetTag[$tagRepo->tag_id];
    }
    //dd($itemList->toArray());
    //dd($TagRepoById);
    foreach($itemList as $item) {
        $item['tags'] = isset($TagRepoById[$item->id]) ? $TagRepoById[$item->id] : [] ;
    }
}
//link批量加tag
function addTagsToLink($itemList) {
    $itemsId = array();
    foreach($itemList as $item) {
        $itemsId[] = $item->id;
    }
    $tagLinks = TagItem::whereIn('item_id', deMul($itemsId))->where('tagitem_type', 'App\Link')->get();
    $tagsId = array();
    foreach($tagLinks as $tagLink) {
        $tagsId[] = $tagLink->tag_id;
    }
    $tags = Tag::whereIn('id', deMul($tagsId))->get();
    $GetTag = array();
    foreach($tags as $tag) {
        $GetTag[$tag->id] = $tag->toArray();
    }
    $TagLinkById = array();
    foreach($tagLinks as $tagLink) {
        $TagLinkById[$tagLink->item_id][] = $GetTag[$tagLink->tag_id];
    }
    foreach($itemList as $item) {
        $item['tags'] = isset($TagLinkById[$item->id]) ? $TagLinkById[$item->id] :null ;
    } 
}

//传入object , showall 1为显示全部 2为权限不足隐藏隐藏仓库
function getRecentItems($repoList, $showAll, $limit) {
    if( empty($repoList->toArray() ) ) {
        return [];
    }
    $reposId = array();
    foreach($repoList as $repo) {
        $reposId[] = $repo->id;
        //$getRepoById[$repo->id]['repository'] = $repo;
    }
    
    if($showAll) {
        $recentItems = Repolist::whereIn('repo_id', $reposId)
                                 ->orderBy('updated_at', 'DESC')
                                 ->get();
    }
    else {
        $recentItems = Repolist::whereIn('repo_id', $reposId)
                                 ->where('status', 1)
                                 ->orderBy('updated_at', 'DESC')
                                 ->get();    
    }
    //1为links，2为repositaries
    $getItemById = array();
    $searchRepo = array();
    $searchLink = array();
    foreach ($recentItems as $recentItem) {        
        if($recentItem->type == 1) {
            $getItemById[1][$recentItem->item_id][] = $recentItem->repo_id;
            $searchLink[] = $recentItem->item_id;
        }
        if($recentItem->type == 2) {
            $getItemById[2][$recentItem->item_id][] = $recentItem->repo_id;
            $searchRepo[] = $recentItem->item_id;
        } 
    }
    if(!empty($searchLink)) {
        $recentLinks = Link::whereIn('id', $searchLink)
                        ->orderBy('updated_at', 'DESC')
                        ->get();
        addTagsToLink($recentLinks);
        foreach($recentLinks as $recentLink) {
        $getRecentById[1][$recentLink->id][] = $recentLink;
    }
    }
    if(!empty($searchRepo)) {
        $recentRepos = Repository::whereIn('id', $searchRepo)
                            ->orderBy('updated_at', 'DESC')
                            ->get();
        addTagsToRepo($recentRepos);
        foreach($recentRepos as $Repo) {
            $getRecentById[2][$Repo->id] = $Repo;
        }
    }  
    $setRepoById = array();   
    foreach ($recentItems as $recentItem) {
        if( count(isset($setRepoById[$recentItem->repo_id]['recentItems'])?
                            $setRepoById[$recentItem->repo_id]['recentItems']:null ) >= $limit ) {
          continue; 
        }
        if($recentItem->type == 1) { 
            $setRepoById[$recentItem->repo_id][] = [
                                                'link' => $getRecentById[$recentItem->type][$recentItem->item_id], 
                                                'type' => $recentItem->type
                                                ];
        }
        if($recentItem->type == 2) {
            $setRepoById[$recentItem->repo_id][] = [
                                                'repository' => $getRecentById[$recentItem->type][$recentItem->item_id], 
                                                'type' => $recentItem->type
                                                ];
        }
    }
    $return = array();
    foreach($repoList as $repo) {
        $return[] = ['repository' => $repo, 'recentItems' => isset($setRepoById[$repo->id])?$setRepoById[$repo->id]:[] ];
    }
    return $return;
}