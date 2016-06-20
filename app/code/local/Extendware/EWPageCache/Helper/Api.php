<?php /*obfv1*/
// Copyright © 2016 Extendware
// Are you trying to customize your extension? Contact us (http://www.extendware.com/contacts/) and we can help!
// Please note, not all files are encoded and different extensions have different levels of encoding.
// We are always happy to provide guideance if you are experiencing an issue!



/**
 * Below are methods found in this class
 *
 * @method mixed public __construct()
 * @method mixed public addCategoryIdsAsTagsForSave(array $ids = array())
 * @method mixed public addCmsPageIdsAsTagsForSave(array $ids = array())
 * @method mixed public addProductIdsAsTagsForSave(array $ids = array())
 * @method mixed public addTagsForSave(array $tags)
 * @method mixed public cleanCache($mode = Zend_Cache::CLEANING_MODE_ALL, array $tags = array(), $realTime = null, $flushBlockCache = null)
 * @method mixed public cleanLighteningCache()
 * @method mixed public flushPagesByGroup(array $groups = array(), array $storeIds = array())
 * @method mixed public flushPagesMatchingAnyCacheKey(array $keys)
 * @method mixed public flushPagesMatchingAnyTag(array $tags, $realTime = null, $clearCache = false)
 * @method mixed public getCacheBackend()
 * @method mixed public getCategoryIdsFromProductIds(array $ids)
 * @method mixed public getLinkedProductIdsFromProductIds(array $ids = array(), array $typeIds = array())
 * @method mixed public getParentCategoryIdsFromCategoryIds(array $ids)
 * @method mixed public getParentProductIdsFromProductIds(array $ids = array())
 * @method mixed public getTagsForFlushFromCategoryIds(array $ids, $type = 'default', $clearCache = false)
 * @method mixed public getTagsForFlushFromCmsPageIds(array $ids, $type = 'default', $clearCache = false)
 * @method mixed public getTagsForFlushFromProductIds(array $ids, $type = 'default', $clearCache = false)
 * @method mixed public getTagsFromCategoryIds(array $ids)
 * @method mixed public getTagsFromCmsPageIds(array $ids)
 * @method mixed public getTagsFromProductIds(array $ids)
 * @method mixed public isCachedByCacheKey($cacheKey)
 * @method mixed public setCacheLifetimeForSave($seconds)
 * @method mixed public setIgnoreFlushes($bool)
 * @method mixed public setPageIsCacheable($bool)
 * @method mixed public setWillBeLighteningCacheLoadable($bool)
 * @method mixed public setWillBePrimaryCacheLoadable($bool)
 *
 */

$_F=__FILE__;$_X="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";$_D=strrev("edoced" . "_46esab");eval(gzuncompress($_D($_X)));
