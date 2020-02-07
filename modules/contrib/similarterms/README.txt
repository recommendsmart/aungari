INTRODUCTION
------------

This Drupal module provides context for content items
by displaying a Block of other similar content.
Similarity is based on the Taxonomy Terms assigned to content.
Blocks are available based on similarity within each of the defined Vocabularies
for a site as well as a Block for similarity within all vocabularies.

CONFIGURATION
-------------

Configuration is accomplished per view:

- Create a View and add a contextual filter "Similar by terms: Nid".
- Add a "Similar by terms: Similarity" sort criteria.
- (Optional) Add the "Similar by terms: Similarity" field to output. The
similarity field can be configured to output the count of matching
terms or a percentage.
- Save the View and place the block on a specific content type.
