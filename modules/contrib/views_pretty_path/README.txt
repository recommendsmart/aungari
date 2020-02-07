CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * Extending
 * Maintainers


INTRODUCTION
------------

Views Pretty Path rewrites URLs associated with Views into into a more user- and
SEO-friendly format.

For example, Views Pretty Path would take this URL:

http://example.com/blog?keys=blockchain&field_topic_target_id%5B54%5D=54&field_asset_type_target_id%5B14%5D=14

And rewrite it as:

http://example.com/blog/filter/assets/article/topics/technology/keywords/blockchain.


REQUIREMENTS
------------

No special requirements.


RECOMMENDED MODULES
-------------------

No recommended modules.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
   See: https://www.drupal.org/node/895232 for further information.


CONFIGURATION
-------------

The configuration form can be found at /admin/config/views-pretty-path.

Set the paths to rewrite (prepending a '/' slash), the view each path
will be associated with, and the display of that view from which filters will be
considered by Views Pretty Path during rewriting. Views Pretty Path will then
check against this list, and if a request path matches one of the paths set in
the form, query parameters will be rewritten according to the filters in the
selected view.

A mapping betweeen views filter identifiers and user-defined names can also be
set (e.g. 'field_topic_target_id' is rewritten as 'topics'). Use the following
syntax, each rule separated by a new line: filter_identifier|name

Lastly, a filter subpath can be set (i.e.
'http://example.com/blog/filter/{term1}/{term2}' has the subpath of '/filter').
The default subpath is '/filter'.

EXTENDING
---------

A view may use a filter that is currently not supported by Views Pretty Path. In
that case, a custom Views Pretty Path filter handler service can be created in a
custom module that implements
Drupal\views_pretty_path\FilterHandlers\ViewsPrettyPathFilterHandlerInterface,
extends Drupal\views_pretty_path\FilterHandlers\AbstractFilterHandler and uses
the 'views_pretty_path_filter_handler' Symfony service tag. For an example, see
views_pretty_path.services.yml and
Drupal\views_pretty_path\FilterHandlersTextFilterHandler. Implement the
getTargetedFilterPluginIds() method to select which Views filter plugin IDs to
target with a custom filter handler.


MAINTAINERS
-----------

Current maintainers:
 * Matt Schaff (gwolfman) - https://www.drupal.org/u/gwolfman

This project has been sponsored by:
 * O3 World
   O3 World is a digital agency in Philadelphia that builds great products,
   transforms digital experiences, and takes your most innovative ideas to
   market.
