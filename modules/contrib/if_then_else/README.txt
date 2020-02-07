CONTENTS OF THIS FILE
---------------------
   
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


 INTRODUCTION
------------

Ifthenelse module provides a graphical user interface to set
different rules which can work as a replacement of programmatic hook based
approach to add custom actions on different hooks and events.

The goal of this module is to provide site builders with an easy to
use interface to build rules ( code snippets ) based on different 
events, conditions and actions.

The benefit of doing this to allow site builders to build sites faster 
with custom rules they can build.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/if_then_else

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/if_then_else

Currently supported Events, Actions and Conditions
----------------------------------------------------------
Events:
After Deleting Entity, After Saving Existing Entity, After Saving New Entity, 
Before Saving Entity, Cron Run, Entity Is Viewed, Entity Load, Form Load, 
Form Validate, Init, System Log Entry Is Created, User Login, User Logout,
View Load

Conditions:
Boolean OR, Compare Two Number Inputs, Compare Two String Inputs, Current Theme, 
Data Value Empty, Entity Bundle, Entity Has Field, Entity Type, Form Class, 
List Contains Item, List Count Comparison, Path Has URL Alias,
Periodic Execution, URL Alias Exists, User Has Entity Field Access,
User Role, User Status,

Actions:
Add Item To List, Add Form Field, Add Numbers, Add User Roles, Ban IP Address, 
Block User, Boolean NOT, Calculate Value, Clear Entity Cache, Convert Data Type, 
Create New Entity, Create URL Alias, Delete Entity, Delete URL Alias,
Deny Field Access, Get Entity Field Value, Get Form Field Value,
Get User Roles, Grant Field Access, Make Fields Required, Make Node Non-sticky,
Make Node Sticky, Page Redirect, Parse Image Field, Parse Link Field,
Parse Text Long Field, Parse Text With Summary Field, Promote Node,
Publish Node, Remove Item From List, Remove User Roles, Save Entity,
Send Account Email, Send Email, Set Default Form Field Value, Set Form Error,
Set Entity Field Value, Set Form Field Value, Set Message, Set Variable,
Subtract Numbers,Unblock User, Unpublish Node, Un-promote Node

REQUIREMENTS
------------

This module doesn't have any dependency on any other contributed module.


INSTALLATION
------------
 
 * Install as you would normally install a contributed Drupal module. Visit:
   https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
   for further information.



CONFIGURATION
-------------
 
 * Configure Rules Debugging in 
  Administration » Configuration » System » Ifthenelse configuration

   - Select the checkbox and save to enable debugging of Ifthenelse rules.


MAINTAINERS
-----------

Current maintainers:
 * Neerav Mehata (neeravbm) - https://www.drupal.org/user/1067380
 * Vishal Khialani (vishalkhialani) - https://www.drupal.org/user/479870
 * Akshay Kelotra (akshay.kelotra) - https://www.drupal.org/user/3582438

This project has been sponsored by:
 * Red Crackle
   Specialized in consulting and planning of Drupal powered sites, Red Crackle
   offers development, upgrade, customization, migration and Performance Tuning.
   Visit http://redcrackle.com/ for more information.
