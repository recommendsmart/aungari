Advance Link attributes widget provides an additional widget for the link field found in Drupal core.

The widget allows users to set following attributes/options on their link.

- Target
- a class
- a class for an icon
- visibility for user roles

##Installation and configuration overview

Installation and configuration overview
- Enable the module like normal
- edit the Link Field widget using '**Manage form display**' and select the 'Advance Link Attributes' widget, by default all options a disabled and works link default link widget.
- Also '**Manage Display**' format
### Manage Form Display Widget Settings

####Class Settings
you can have a global class list (/admin/config/advance_link_attributes) or you can define for every single field. you have following options

- Disabled (default)
- Global List
- Custom List

####Icon
[ ] Enable Icon 
####User Roles
[ ] Enable User Roles

###Field Formatter (Manage Display) 
Select Advance Link Attributes formatter and you have following options.

- Class Option (where must be added the class selected)
    - Link Element (as attribute class of tag "a")
    - Parent Element (as attribute class of parent element)
- Icon Position
    - As tag "i" inside element
    - As a class
    - As data-attr
- Role Visibility
    - Hide (No render)
    - Visually Hidden (Rendered but hidden)
