<?php
/**
 * CSSTidy - CSS Optimiser Interface
 * This file produces an XHTML interface for optimising CSS code
 *
 * Copyright 2005, 2006, 2007 Florian Schmitz
 *
 * This file is part of CSSTidy.
 *
 *  CSSTidy is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation; either version 2.1 of the License, or
 *   (at your option) any later version.
 *
 *   CSSTidy is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Lesser General Public License for more details.
 * 
 *   You should have received a copy of the GNU Lesser General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @package csstidy
 * @author Florian Schmitz (floele at gmail dot com) 2005-2007
 * @author Brett Zamir (brettz9 at yahoo dot com) 2007
 * @author Jakub Onderka (acci at acci dot cz) 2011
 */


require_once __DIR__ . '/lib/CSSTidy.php';
require_once __DIR__ . '/lang.inc.php';


if (get_magic_quotes_gpc()) {
	if (isset($_REQUEST['css_text'])) {
		$_REQUEST['css_text'] = stripslashes($_REQUEST['css_text']);
	}
	if (isset($_REQUEST['custom'])) {
		$_REQUEST['custom'] = stripslashes($_REQUEST['custom']);
	}
	if (isset($_COOKIE['custom_template'])) {
		$_COOKIE['custom_template'] = stripslashes($_COOKIE['custom_template']);
	}
}

function rmdirr($dirname,$oc=0)
{
	// Sanity check
	if (!file_exists($dirname)) {
	  return false;
	}
	// Simple delete for a file
	if (is_file($dirname) && (time()-fileatime($dirname))>3600) {
	   return unlink($dirname);
	}
	// Loop through the folder
	if(is_dir($dirname))
	{
	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {
	   // Skip pointers
	   if ($entry === '.' || $entry === '..') {
		   continue;
	   }
	   // Recurse
	   rmdirr($dirname.'/'.$entry,$oc);
	}
	$dir->close();
	}
	// Clean up
	if ($oc==1)
	{
		return rmdir($dirname);
	}
}

function options(array $options = array(), $selected = null, $labelIsValue = false) {
    $html = '';

    foreach ($options as $value => $label) {
        if (is_array($label)) {
            
        }

        $label = htmlspecialchars($label, ENT_QUOTES, 'utf-8');
        $value = $labelIsValue ? $label
                               : htmlspecialchars($value, ENT_QUOTES, 'utf-8');

        $html .= '<option value="'.$value.'"';
        if ($value == $selected) {
            $html .= ' selected="selected"';
        }
        $html .= '>'.$label.'</option>';
    }

    if (empty($html)) {
        return '<option value="0">---</option>';
    }

    return $html;
}

$css = new CSSTidy\CSSTidy;
$is_custom = isset($_REQUEST['custom']) && !empty($_REQUEST['custom']) && isset($_REQUEST['template']) && ($_REQUEST['template'] === '4');
if($is_custom)
{
    setcookie ('custom_template', $_REQUEST['custom'], time()+360000);
}

rmdirr('temp');

if(isset($_REQUEST['case_properties'])) $css->configuration->setCaseProperties((int) $_REQUEST['case_properties']);
if(isset($_REQUEST['lowercase'])) $css->configuration->setLowerCaseSelectors(true);
if(!isset($_REQUEST['compress_c']) && isset($_REQUEST['post'])) $css->configuration->setCompressColors(false);
if(!isset($_REQUEST['compress_fw']) && isset($_REQUEST['post'])) $css->configuration->setCompressFontWeight(false);
if(isset($_REQUEST['merge_selectors'])) $css->configuration->setMergeSelectors((int) $_REQUEST['merge_selectors']);
if(isset($_REQUEST['optimise_shorthands'])) $css->configuration->setOptimiseShorthands((int) $_REQUEST['optimise_shorthands']);
if(!isset($_REQUEST['rbs']) && isset($_REQUEST['post'])) $css->configuration->setRemoveBackSlash(false);
if(isset($_REQUEST['preserve_css'])) $css->configuration->setPreserveCss(true);
if(isset($_REQUEST['sort_sel'])) $css->configuration->setSortSelectors(true);
if(isset($_REQUEST['sort_de'])) $css->configuration->setSortProperties(true);
if(isset($_REQUEST['remove_last_sem'])) $css->configuration->setRemoveLastSemicolon(true);
if(isset($_REQUEST['discard'])) $css->configuration->setDiscardInvalidProperties(true);
if(isset($_REQUEST['css_level'])) $css->configuration->setCssLevel($_REQUEST['css_level']);
if(isset($_REQUEST['timestamp'])) $css->configuration->setAddTimestamp(true);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>
      <?php echo $lang[$l][0]; echo $css::getVersion(); ?>)
    </title>
    <link rel="stylesheet" href="cssparse.css" type="text/css" >
    <script type="text/javascript">
    function enable_disable_preserve()
    {
        var inputs = {'sort_sel': true, 'sort_de': true, 'optimise_shorthands': true, 'merge_selectors': true, 'none': false};
        var preserverCssChecked = document.getElementById('preserve_css').checked;

        for(var key in inputs) {
            document.getElementById(key).disabled = preserverCssChecked ? inputs[key] : !inputs[key];
        }
    }
    function ClipBoard()
    {
		if (window.clipboardData) { // Feature testing
			window.clipboardData.setData('Text',document.getElementById("copytext").innerText);
		} else if (navigator.userAgent.indexOf('Gecko') != -1
					&& navigator.userAgent.indexOf('Apple') == -1
					) {
			try {
				netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
				const gClipboardHelper = Components.classes["@mozilla.org/widget/clipboardhelper;1"].
                                    getService(Components.interfaces.nsIClipboardHelper);
									gClipboardHelper.copyString(document.getElementById("copytext").innerHTML);
			}
			catch (e) {
				alert(e+"\n\n"+"<?php echo $lang[$l][66] ?>");
			}
		}
		else {
			alert("<?php echo $lang[$l][60]; ?>");
		}
    }
    </script>
  </head>
  <body onload="enable_disable_preserve()">
    <div><h1 style="display:inline">
      <?php echo $lang[$l][1]; ?>
    </h1>
    <?php echo $lang[$l][2]; ?> <a
      href="http://csstidy.sourceforge.net/">CSSTidy</a> <?php echo $css::getVersion(); ?>)
    </div><p>
    <?php echo $lang[$l][39]; ?>: <a hreflang="en" href="?lang=en">English</a> <a hreflang="de" href="?lang=de">Deutsch</a> <a hreflang="fr" href="?lang=fr">French</a> <a hreflang="zh" href="?lang=zh">Chinese</a></p>
    <p><?php echo $lang[$l][4]; ?>
      <?php echo $lang[$l][6]; ?>
    </p>

    <form method="post">
      <div>
        <fieldset id="field_input">
          <legend><?php echo $lang[$l][8]; ?></legend> <label for="css_text"
          class="block"><?php echo $lang[$l][9]; ?></label><textarea id="css_text" name="css_text" rows="20" cols="35"><?php if(isset($_REQUEST['css_text'])) echo htmlspecialchars($_REQUEST['css_text'], ENT_QUOTES, "utf-8"); ?></textarea>
            <label for="url"><?php echo $lang[$l][10]; ?></label> <input type="text"
          name="url" id="url" <?php if(isset($_REQUEST['url']) &&
          !empty($_REQUEST['url'])) echo 'value="',htmlspecialchars($_REQUEST['url'], ENT_QUOTES, 'utf-8'),'"'; ?>
          size="35"><br>
          <input type="submit" value="<?php echo $lang[$l][35]; ?>" id="submit">
        </fieldset>
        <div id="rightcol">
          <fieldset id="code_layout">
            <legend><?php echo $lang[$l][11]; ?></legend> <label for="template"
            class="block"><?php echo $lang[$l][12]; ?></label> <select
            id="template" name="template" style="margin-bottom:1em;">
              <?php
                $num = (isset($_REQUEST['template'])) ? intval($_REQUEST['template']) : 1;
                echo options(array(3 => $lang[$l][13], 2 => $lang[$l][14], 1 => $lang[$l][15], 0 => $lang[$l][16], 4 => $lang[$l][17]), $num);
              ?>
            </select><br>
            <label for="custom" class="block">
            <?php echo $lang[$l][18]; ?> </label> <textarea id="custom"
            name="custom" cols="33" rows="4"><?php
               if($is_custom) echo
              htmlspecialchars($_REQUEST['custom'], ENT_QUOTES, 'utf-8');
               elseif(isset($_COOKIE['custom_template']) &&
              !empty($_COOKIE['custom_template'])) echo
				htmlspecialchars($_COOKIE['custom_template'], ENT_QUOTES, 'utf-8');
               ?></textarea>
          </fieldset>
          <fieldset id="options">
         <legend><?php echo $lang[$l][19]; ?></legend>

            <input onchange="enable_disable_preserve()" type="checkbox" name="preserve_css" id="preserve_css"
                   <?php if($css->configuration->getPreserveCss()) echo 'checked="checked"'; ?>>
            <label for="preserve_css" title="<?php echo $lang[$l][52]; ?>" class="help"><?php echo $lang[$l][51]; ?></label><br>


            <input type="checkbox" name="sort_sel" id="sort_sel"
                   <?php if($css->configuration->getSortSelectors()) echo 'checked="checked"'; ?>>
            <label for="sort_sel" title="<?php echo $lang[$l][41]; ?>" class="help"><?php echo $lang[$l][20]; ?></label><br>


            <input type="checkbox" name="sort_de" id="sort_de"
                   <?php if($css->configuration->getSortProperties()) echo 'checked="checked"'; ?>>
            <label for="sort_de"><?php echo $lang[$l][21]; ?></label><br>


            <label for="merge_selectors"><?php echo $lang[$l][22]; ?></label>
            <select style="width:15em;" name="merge_selectors" id="merge_selectors">
              <?php echo options(array('0' => $lang[$l][47], '1' => $lang[$l][48], '2' => $lang[$l][49]), $css->configuration->getMergeSelectors()); ?>
            </select><br>

            <label for="optimise_shorthands"><?php echo $lang[$l][23]; ?></label>
            <select name="optimise_shorthands" id="optimise_shorthands">
            <?php echo options(array($lang[$l][54], $lang[$l][55], $lang[$l][56]), $css->configuration->getOptimiseShorthands()); ?>
            </select><br>


            <input type="checkbox" name="compress_c" id="compress_c"
                   <?php if($css->configuration->getCompressColors()) echo 'checked="checked"';?>>
            <label for="compress_c"><?php echo $lang[$l][24]; ?></label><br>


            <input type="checkbox" name="compress_fw" id="compress_fw"
                   <?php if($css->configuration->getCompressFontWeight()) echo 'checked="checked"';?>>
            <label for="compress_fw"><?php echo $lang[$l][45]; ?></label><br>


            <input type="checkbox" name="lowercase" id="lowercase" value="lowercase"
                   <?php if($css->configuration->getLowerCaseSelectors()) echo 'checked="checked"'; ?>>
            <label title="<?php echo $lang[$l][30]; ?>" class="help" for="lowercase"><?php echo $lang[$l][25]; ?></label><br>


            <?php echo $lang[$l][26]; ?><br>
            <input type="radio" name="case_properties" id="none" value="0"
                   <?php if($css->configuration->getCaseProperties() === 0) echo 'checked="checked"'; ?>>
            <label for="none"><?php echo $lang[$l][53]; ?></label>
            <input type="radio" name="case_properties" id="lower_yes" value="1"
                   <?php if($css->configuration->getCaseProperties() === 1) echo 'checked="checked"'; ?>>
            <label for="lower_yes"><?php echo $lang[$l][27]; ?></label>
            <input type="radio" name="case_properties" id="upper_yes" value="2"
                   <?php if($css->configuration->getCaseProperties() === 2) echo 'checked="checked"'; ?>>
            <label for="upper_yes"><?php echo $lang[$l][29]; ?></label><br>

            <input type="checkbox" name="rbs" id="rbs"
                   <?php if($css->configuration->getRemoveBackSlash()) echo 'checked="checked"'; ?>>
            <label for="rbs"><?php echo $lang[$l][31]; ?></label><br>


            <input type="checkbox" id="remove_last_sem" name="remove_last_sem"
                   <?php if($css->configuration->getRemoveLastSemicolon()) echo 'checked="checked"'; ?>>
   			<label for="remove_last_sem"><?php echo $lang[$l][42]; ?></label><br>


            <input type="checkbox" id="discard" name="discard"
                   <?php if($css->configuration->getDiscardInvalidProperties()) echo 'checked="checked"'; ?>>
            <label for="discard"><?php echo $lang[$l][43]; ?></label>
            <select name="css_level"><?php echo options(array('CSS3.0', 'CSS2.1','CSS2.0','CSS1.0'), $css->configuration->getCssLevel(), true); ?></select><br>


            <input type="checkbox" id="timestamp" name="timestamp"
                   <?php if($css->configuration->getAddTimestamp()) echo 'checked="checked"'; ?>>
   			<label for="timestamp"><?php echo $lang[$l][57]; ?></label><br>

			<input type="checkbox" id="whole_file" name="whole_file"
                   <?php if(isset($_REQUEST['whole_file'])) echo 'checked="checked"'; ?>>
   			<label for="whole_file"><?php echo $lang[$l][63]; ?></label><br>


            <input type="checkbox" name="file_output" id="file_output" value="file_output"
                   <?php if(isset($_REQUEST['file_output'])) echo 'checked="checked"'; ?>>
            <label class="help" title="<?php echo $lang[$l][34]; ?>" for="file_output">
				<strong><?php echo $lang[$l][33]; ?></strong>
			</label><br>

          </fieldset>
        <input type="hidden" name="post">
        </div>
      </div>
    </form>
    <?php

    $file_ok = false;
    $result = false;

    $url = (isset($_REQUEST['url']) && !empty($_REQUEST['url'])) ? $_REQUEST['url'] : false;

	if(isset($_REQUEST['template']))
	{
		switch($_REQUEST['template'])
		{
			case 4:
                if($is_custom) {
                    $css->configuration->loadTemplateFromString($_REQUEST['custom']);
                }
                break;

			case 3:
                $css->configuration->loadPredefinedTemplate(CSSTidy\Configuration::HIGHEST_COMPRESSION);
                break;

			case 2:
                $css->configuration->loadPredefinedTemplate(CSSTidy\Configuration::HIGH_COMPRESSION);
                break;

			case 0:
                $css->configuration->loadPredefinedTemplate(CSSTidy\Configuration::LOW_COMPRESSION);
                break;
		}
	}

    if($url) {
    	if(substr($_REQUEST['url'], 0, 7) !== 'http://') {
			$_REQUEST['url'] = 'http://' . $_REQUEST['url'];
		}
        $output = $css->parseFromUrl($_REQUEST['url']);
    } elseif(isset($_REQUEST['css_text']) && strlen($_REQUEST['css_text']) > 5) {
        $output = $css->parse($_REQUEST['css_text']);
    }

    if (isset($output) && $output) {
        $ratio = $output->getRatio();
        $diff = $output->getDiff();

        if(isset($_REQUEST['file_output']))
        {
            $filename = md5(mt_rand().time().mt_rand());
            if (!is_dir('temp')) {
                $madedir = mkdir('temp');
                if (!$madedir) {
                    print 'Could not make directory "temp" in '.dirname(__FILE__);
                    exit;
                }
            }
            $handle = fopen('temp/'.$filename.'.css','w');
            if($handle) {
                if(fwrite($handle, $output->plain()))
                {
                    $file_ok = true;
                }
            }
            fclose($handle);
        }
        if($ratio>0) $ratio = '<span style="color:green;">'.$ratio.'%</span>
    ('.$diff.' Bytes)'; else $ratio = '<span
    style="color:red;">'.$ratio.'%</span> ('.$diff.' Bytes)';
        if(count($css->logger->getMessages()) > 0): ?>
        <fieldset id="messages"><legend>Messages</legend>
			<div><dl><?php
			foreach($css->logger->getMessages() as $line => $array)
			{
				echo '<dt>',$line,'</dt>';
				$array_size = count($array);
				for($i = 0; $i < $array_size; ++$i)
				{
					echo '<dd class="',$array[$i][CSSTidy\Logger::TYPE],'">',$array[$i][CSSTidy\Logger::MESSAGE],'</dd>';
				}
			}
			?></dl></div>
        </fieldset>
        <?php endif; ?>
        <fieldset>
          <legend><?php echo $lang[$l][37] ?>: <?php echo $output->size(CSSTidy\Output::INPUT) ?> KB (gzipped <?php echo $output->gzippedSize(CSSTidy\Output::INPUT) ?> KB), <?php echo $lang[$l][38] ?>: <?php echo $output->size(CSSTidy\Output::OUTPUT) ?> KB (gzipped <?php echo $output->gzippedSize(CSSTidy\Output::OUTPUT) ?> KB), <?php echo $lang[$l][36] ?>: <?php echo $ratio ?>
        <?php
        if($file_ok)
        {
            echo ' - <a href="temp/',$filename,'.css">Download</a>';
        }
        echo ' - <a href="javascript:ClipBoard()">',$lang[$l][58],'</a>';
        echo '</legend>';
        echo '<code id="copytext">';
        echo $output->formatted();
        echo '</code></fieldset><div><br></div>';

		echo '<fieldset class="code_output"><legend>',$lang[$l][64],'</legend>';
        echo '<textarea rows="10" cols="80">';

		if (isset($_REQUEST['whole_file'])) {
			echo htmlspecialchars($output->formattedPage(false, ''), ENT_QUOTES, 'utf-8');
		} else {
			echo htmlspecialchars("<code id=\"copytext\">\n{$output->formatted()}\n</code>", ENT_QUOTES, 'utf-8');
		}
		echo '</textarea></fieldset>';
		echo '<fieldset class="code_output"><legend>',$lang[$l][65],'</legend>';
		echo '<textarea rows="10" cols="30">';

		echo file_get_contents('cssparsed.css');
		echo '</textarea>';

		echo '</fieldset><p><a href="javascript:scrollTo(0,0)">&#8593; ',$lang[$l][59],'</a></p>';

    } elseif(isset($_REQUEST['css_text']) || isset($_REQUEST['url'])) {
        echo '<p class="important">',$lang[$l][28],'</p>';
     }
     ?>
    <p style="text-align:center;font-size:.8em;clear:both;">
      <?php echo $lang[$l][61] ?> <a
      href="http://csstidy.sourceforge.net/contact.php"><?php echo $lang[$l][62] ?></a>.
    </p>
  </body>
</html>