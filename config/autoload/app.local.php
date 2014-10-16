<?php
/**
 * App Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */
return array(
        'app' => array ( 
        		'45893' => array (
        				'name' => 'MarkSend',
        				'url' => 'http://clickmarkdigital.com/',
        				'token' => '4e1650a3a4d9a5e8a879011bcecbc262'
        		),
        		'79216' => array (
        				'name' => 'ClickMark',
		        		'url' => 'http://clickmark.dev/',
		        		'token' => 'c6fd603ccc5ff5898097dd4ed0d2a93a'
		        	),
        )
);