<?php
/*************************************************************************************
 * r.php 
 
 
  *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/
 
$language_data = array (
	'LANG_NAME' => 'R',
	'COMMENT_SINGLE' => array(1 => '#'),
	'COMMENT_MULTI' => array(';|' => '|;'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array('"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
		 'if','else','for','in','while','repeat','break','next','function','return'
			)
		),
	'SYMBOLS' => array(
		'(', ')', '{', '}', '[', ']', '!', '%', '^', '&', '/','+','-','*','=','<','>',';','|','<-','->'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #a020f0;'
			),
		'COMMENTS' => array(
			1 => 'color: #b22222; font-style: italic;',
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #000099; font-weight: bold;'
			),
		'BRACKETS' => array(
			0 => 'color: #66cc66;'
			),
		'STRINGS' => array(
			0 => 'color: #ff0000;'
			),
		'NUMBERS' => array(
			0 => 'color: #cc66cc;'
			),
		'METHODS' => array(
			0 => 'color: #202020;'
			),
		'SYMBOLS' => array(
			0 => 'color: #78aaac;'
			),
		'REGEXPS' => array(
                      1 => 'color:#228b22;',
                      3 => 'color:green',
                      4 => 'color:green',
			),
		'SCRIPT' => array(
			)
		),
	'URLS' => array(
		),
	'OOLANG' => false,
	'OBJECT_SPLITTERS' => array(
		),
'REGEXPS' => array(
    1 => array(
        GESHI_SEARCH => '(TRUE|FALSE|NULL|NA|NaN|Inf)',
        GESHI_REPLACE => '\\1',
        GESHI_MODIFIERS => '',
        GESHI_BEFORE => '',
        GESHI_AFTER => ''
    ),
    3 => array(
        GESHI_SEARCH => '(^.{1,2}|\n)( ?)(\\\\\+|>)( )',
        GESHI_REPLACE => ' \\3',
        GESHI_MODIFIERS => '',
        GESHI_BEFORE => '\\1',
        GESHI_AFTER => '\\4'
    ),
    4 => array(
        GESHI_SEARCH => '([a-zA-Z|\.]\w*)((\\\\\.)+\w*\s*[,=\&|\\\\][^\.\(|\\\\])',
        GESHI_REPLACE => '\\1',
        GESHI_MODIFIERS => '',
        GESHI_BEFORE => '',
        GESHI_AFTER => '\\2'
    ),
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		)
);
 
?>