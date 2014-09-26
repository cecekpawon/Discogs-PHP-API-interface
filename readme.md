##  Discogs PHP API interface

@cecekpawon - https://blog.thrsh.net


```php
require_once("yod.discogs.class.php");

/**
 * Options (all optional): array with current default value
 *
 *   return: array / object
 *
 *   only_success
 *     true: return empty if request was rejected
 *     false: return with error messages
 *
 *   debug
 *     true: return JSON string with: http code, results & url
 *     false: return array / object
 *
 *   as_array
 *     true: return as array results
 *     false: return as object results
 *
 *   native_user_agent
 *     true: use class user agent
 *     false: use 'user' user agent
 */


$discogs = new Discogs(
  array(
    "only_success" => false,
    "debug" => false,
    "as_array" => false,
    "native_user_agent" => true
  )
);
```

### Get release

```php
/**
 * @param  integer $release_id [required]
 */

$req = $discogs->releases(4538856);
```

### Get a master (accepts pagination params)

```php
/**
 * @param  integer $master_id [required]
 * @param  boolean $versions  [optional]
 * @param  array   $param     [optional]
 */

// Master release info
$req = $discogs->masters(552521);

// With all versions
$req = $discogs->masters(552521, true);

// With pagination params
$req = $discogs->masters(552521, true, array("page" => 1, "per_page" => 3));
```

### Get artists

```php
/**
 * @param  integer $artist_id [required]
 * @param  boolean $releases  [optional]
 */

// Artist info
$req = $discogs->artists(581632);

// With additional releases
$req = $discogs->artists(581632, true);
```

### Get a label (accepts pagination params)

```php
/**
 * @param  integer $label_id [required]
 * @param  boolean $releases [optional]
 * @param  array   $param    [optional]
 */

// Label info
$req = $discogs->labels(540515);

// With additional releases
$req = $discogs->labels(540515, true);

// With pagination params
$req = $discogs->labels(540515, true, array("page" => 1, "per_page" => 3));
```

### Search query

```php
/**
 * @param  array  $param [required]
 * @param  string $query [optional]
 */

$req = $discogs->search(array("page" => 1, "type" => "artist"), "extreme decay");
```