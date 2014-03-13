Set data
========

You can use fetched data to avoid unnecessary queries.

**Note**: With setData, operators `Equals` and `Contains` support regular expression.

Imagine a user with bookmarks represented by a type (youtube, twitter,...) and a link.  
Current behavior to display the bookmarks of a user:

```php
<?php
...
public function displayUserBookmarksAction()
{
    // Get the user from context
    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
        throw new AccessDeniedException('This user does not have access to this section.');
    }

    // Instanciate the grid
    $grid = $this->get('grid');

    // Define the source of the grid
    $source = new Entity('MyProjectMyBundle:Bookmark');

    // Add a where condition to the query to get only bookmarks of the user
    $tableAlias = $source->getTableAlias();

    $source->manipulateQuery(function ($query) use ($tableAlias, $user) {
        $query->where($tableAlias . '.member = '.$user->getId());
    });

    $grid->setSource($source);

    return $grid->getGridResponse();
}
```

Bookmarks are related by the logged user, so you can retrieve it directly from the user instance and use it for the grid:

## Usage
```php
<?php
...
$source->setData($data);

$grid->setSource($source);
...
```

## Method parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|data|array||Array of data compatible with the source.|

## Example
```php
<?php
...
public function displayUserBookmarksAction()
{
    // Get the user from context
    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
        throw new AccessDeniedException('This user does not have access to this section.');
    }

    // Instanciate the grid
    $grid = $this->get('grid');

	$source = new Entity('MyProjectMyBundle:Bookmark');
	
	// Get bookmarks related to the user and set the grid data 
    $source->setData($user->getBookmarks());
	
    // Define the source of the grid
    $grid->setSource($source);

    return $grid->getGridResponse();
}
...
```

## Unsupport feature

Aggregate DQL functions and GroupBy option are not supported.
