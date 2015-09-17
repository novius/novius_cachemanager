# Novius CacheManager

This application provides a behaviour helping handling the cache invalidation on a model.

## Orm_Behaviour_CacheManager


### relations

The behaviour will try relationships on a item when updated or deleted.

If any of these relations has the Behaviour_Url_Enhancer, the cache will be cleared for the related items.

You can use the php accessor "->" to parse any children of a relationship. Only the last children model will be cleared.

The behaviour won't cascade, and won't trigger its homonyms on related models. (see below)

```php
// class post.model.php
protected static $_behaviours = array(
    'Novius\CacheManager\Orm_Behaviour_CacheManager' => array(
        'relations' => array(
            'comments',
        )
    ),
);

// class category.model.php
protected static $_behaviours = array(
    'Novius\CacheManager\Orm_Behaviour_CacheManager' => array(
        'relations' => array(
            'posts', // Will clear the posts but not the comments
            'posts->comments', // Will clear the comments only
        )
    ),
);
```

## Configuration Example

```php
protected static $_behaviours = array(
    'Novius\CacheManager\Orm_Behaviour_CacheManager' => array(
        'relations' => array(
            'category', // One item
            'comments', // has many
            'category->posts->comments' // parsing related and clearing only comments
            'category->posts', // If we want to clear the cache of all posts
        )
    ),
);
```