<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /* ThEEditor - Better template editing in EE.
	Copyright (C) 2011  Matt Gilg
	
	This plugin enables simple, clean, and robust editing within EE,
	and has a few features that make it stand on its own.
	 - Syntax Highlighting of EE tags in HTML documents.
	 - Automatic document-type detection for intelligent highlighting
	   of html, css, javascript, and xml.
	 - Javascript error checking and analysis. (inline js ee tags not supported yet)
	 - A variety of editor themes.
	 - Ctrl+Z undo.	  

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.*/

/*
 * ThEEditor - Improved EE template editing.
 *
 * @package			TheEditor
 * @version			1.0
 * @author			Matt Gilg 
 * @copyright		Copyright (c) 2011 VisibleDevelopment <http://www.visibledevelopment.com>
 * @category			Accessories
 */

class Theeditor_acc
{
	var $name					= 'ThEEditor';
	var $id						= 'theeditor';
	var $version				= '1.0';
	var $description			= 'Improved EE template editing.';
	var $sections				= array();
	var $ace_path               = '/assets/js/ace';
 	var $tab_size               = 2;
 	var $show_gutter            = true;
 	var $font_size              = 14;
 	
 	/*
 	 * This field controls the height of the edit window.
 	 * NOTE: this field has no effect when set to less than the size
 	 * 		 of the original edit window.
 	 */
 	var $editor_height          = 0; 	

 	/*
 	 * The $theme variable lets you choose the editor color
 	 * theme.  'pastel_on_dark' is our default, but the available
 	 * themes are:
 	 * 
 	 * clouds, clouds_midnight, cobalt, dawn, eclipse, idle_fingers,
 	 * kr_theme, merbivore, merbivore_soft, mono_industrial, monokai,
 	 * pastel_on_dark, twilight, vibrant_ink, textmate
 	 * 
 	 */
 	var $theme                  = 'pastel_on_dark';
 	
	//=========================================================================

 	
	function set_sections()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('javascript');
	
		if ($this->EE->input->get('M') == "edit_template" ||
			$this->EE->input->get('M') == "update_template" ||
			$this->EE->input->get('M') == "create_new_template" ) {				
				$id = $this->EE->input->get('id');
				$query = $this->EE->db->query("SELECT `template_type` FROM `{$this->EE->db->dbprefix}templates` WHERE template_id={$this->EE->db->escape($id)}");				
				$mode = $this->_get_ace_mode($query->row('template_type'));				
				$this->_include_required($mode);
				$this->_set_styles();
				$this->_refactor_dom();
				$this->_show_editor($mode);															
		}
		
		$this->_cleanup_tab();			
	}


	//=========================================================================
	
	
	function _show_editor($mode){
	$this->EE->cp->add_to_foot(
<<<END
	<script>								
		$(document).ready(function(){	
			var theeditor = ace.edit("theeditor");

			// Set some editor options.
			theeditor.setTheme("ace/theme/{$this->theme}");   		    		
    		theeditor.renderer.setShowGutter({$this->show_gutter});
    		theeditor.getSession().setTabSize({$this->tab_size});
    		
    		// Set some default non-configurable styling.
    		theeditor.setShowPrintMargin(false);
    		
    		// Make the gutter take the same style as the current
    		// cp theme.
    		var bg_color = $("#template_details").css("background-color");
    		var fg_color = $("#template_details").css("color");    		
    		$(".ace_gutter").css({"background-color":bg_color,"color":fg_color});

    		// Set editor mode.
    		var mode = require("ace/mode/{$mode}").Mode;
    		theeditor.getSession().setMode(new mode());

			// Show TheEditor.
    		$("#markItUpTemplate_data").slideUp(function(){
    			$("#theeditor").slideDown(function(){
    				theeditor.renderer.onResize(true);		
    			});
			});

			// Update invisible placeholder.
    		theeditor.getSession().on('change',function(){
    			$("#template_data").text(theeditor.getSession().getValue());
				$("#template_data").val(theeditor.getSession().getValue()); 
    		});
		});				
	</script>

END
				);
	}
	
	
	//=========================================================================

	
	function _cleanup_tab(){
		$this->EE->javascript->output('		
		$("#theeditor.accessory").remove();
		$("a.theeditor").parent("li").remove();
		');	
	}
	
	
	//=========================================================================
	
	
	function _set_styles(){
		$this->EE->cp->add_to_head(
<<<END
		<style>
			#theeditor{
				position:relative; 
				font-size:{$this->font_size}px;
			}
		</style>
END
		);
	}
	
	
	//=========================================================================
	
	
	function _include_required($mode){
		$this->EE->cp->add_to_head(
<<<END
		<script src="{$this->ace_path}/ace.js" type="text/javascript" charset="utf-8"></script>
		<script src="{$this->ace_path}/theme-{$this->theme}.js" type="text/javascript" charset="utf-8"></script>
		<script src="{$this->ace_path}/mode-{$mode}.js" type="text/javascript" charset="utf-8"></script>
END
		);
	}
	
	
	//=========================================================================
	
	
	function _refactor_dom(){
		$this->EE->javascript->output(
<<<END
		var details = $("#template_details").detach();				
    	$("#mainContent .heading").after(details);
    	$("#mainContent .pageContents").before("<div id='theeditor' style='background:#fff;width:100%'></div>");
  		$("#theeditor").hide();    	    	    
    	
  		// Set editor height, with the original editor height as
 	    // the minimum. 
  		var height = $("#markItUpTemplate_data").height();
    	if (height < {$this->editor_height}) height = {$this->editor_height};    	 		
   		$("#theeditor").height(height);
   		    		   		
   		$("#theeditor").text($("#markItUpTemplate_data textarea").text());   			     				
END
		);
	}
	
	
	//=========================================================================
	
	
	function _get_ace_mode($str){
		switch($str){
			case 'css':
				return 'css';
			break;
			case 'webpage':
				return 'html';
			break;
			case 'js':
				return 'javascript';				
			case 'xml':
				return 'xml';
			default: return 'php';
			break;
		}
	}

	
	//=========================================================================	
}
/* Location: ./system/expressionengine/third_party/theeditor/acc.theeditor.php */