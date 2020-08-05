# Files
* `drutopia-tested-global-pinnings.sh`: Holds tested module versions (does not include drupal/core)

# Pinned drupal module versions

## How to use recommended module pinnings

* Find recommended (=tested) module versions in `bin/drutopia-tested-global-pinnings.sh`
* Apply all by running `./vendor/bin/composer-pin-apply web/profiles/contrib/drutopia/bin/drutopia-tested-global-pinnings.sh`

## Why we pin module versions
* On installing a site via composer latest allowed (=according to semantic versions in all composer.json files) dependencies are downloaded: That's what we do not want because there might be new aka untested versions which break the site
* Therefore we maintain the tested module versions in the `bin/drutopia-tested-global-pinnings.sh`

## Common problems
### Why don't we require exact versions in H4C?
...like it is done by [drupal/core-recommended](https://github.com/drupal/core-recommended/blob/8.8.x/composer.json) introduced in 8.8
* We can't because we do not want to force specific versions onto sites but offer a set of working modules. If done like in the mentioned drupal package, a site couldn't ever use another version of a drutopia dependency than required by drutopia.

### I cannot update a module
* tl;dr: Use composer `remove drupal/webform` to unpin the module
* Long version:`composer update drupal/webform` won't do anything because we pinned the webform module version in *composer.json* to a specific version (instead of using semver which is what composer is made for. Composer would only use the *.lock* file which is being updated for webform using the update command. 

### Dependencies cannot be resolved
* tl;dr: Use composer `remove drupal/webform` to unpin the module (sometimes you need to do this for an accidentally pinned submodule as well)
* By pinning modules we restrict the range of possible version combinations. Therefore you might run into unresolved dependency errors e.g. on updating some of the modules

### Running `./vendor/bin/composer-pin-apply web/profiles/contrib/drutopia/bin/drutopia-tested-global-pinnings.sh` fails 
* Try again
* Remove failing patches => Try again
* If patches fail com from upstream and cannot be removed, update module to version where patch applies => Try again

Other things you can try (**with caution!**):
* `rm -rf web/modules/contrib/*` => Try again
* `composer remove drupal/*` (might also remove dependencies not included in pinnings from composer root) => Try again

## Regenerating 

To regenerate the content of `drutopia-tested-global-pinnings.sh`:

* Install and test a site.
* In the root directory of your project, run `composer show drupal/*` to list currently installed versions of all drupal packages.
* Copy and paste the result into `drutopia-tested-global-pinnings.sh`, replacing the previous content.

## Why are some modules pinned to commits?

* None at present
