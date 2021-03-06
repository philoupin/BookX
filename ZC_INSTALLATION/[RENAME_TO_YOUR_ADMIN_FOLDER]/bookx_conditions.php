<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5
 *
 * For latest version and support visit:
 * https://sourceforge.net/p/zencartbookx
 *
 * @package admin
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 0.9.4-revision8 BETA
 * @version $Id: [admin]/bookx_conditions.php 2016-02-02 philou $
 */

/**
 * Product Type Book (BookX) Conditions
 *
 * This file handles creating, editing and deleting
 * condition infos
 *
 */

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($_GET['mID'])) $bookx_condition_id = zen_db_prepare_input($_GET['mID']);
        $condition_sort_order = zen_db_prepare_input($_POST['condition_sort_order']);
        
        $sql_data_array = array('condition_sort_order' => $condition_sort_order);        

        if ($action == 'insert') {

          zen_db_perform(TABLE_PRODUCT_BOOKX_CONDITIONS, $sql_data_array);
          $bookx_condition_id = zen_db_insert_id();
        } elseif ($action == 'save') {
          /*$update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);*/

          zen_db_perform(TABLE_PRODUCT_BOOKX_CONDITIONS, $sql_data_array, 'update', "bookx_condition_id = '" . (int)$bookx_condition_id . "'");
        }

        $languages = zen_get_languages();
        $condition_description_array = $_POST['condition_description'];
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        	$language_id = $languages[$i]['id'];

      	

          $sql_data_array = array('condition_description' => zen_db_prepare_input($condition_description_array[$language_id]));

          if ($action == 'insert' || 
          	  ($action == 'save' && null === bookx_get_condition_description($bookx_condition_id, $language_id))) {
            $insert_sql_data = array('bookx_condition_id' => $bookx_condition_id,
                                     'languages_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            zen_db_perform(TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION, $sql_data_array);
          } elseif ($action == 'save') {
            zen_db_perform(TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION, $sql_data_array, 'update', "bookx_condition_id = '" . (int)$bookx_condition_id . "' and languages_id = '" . (int)$language_id . "'");
          }
        }

        zen_redirect(zen_href_link(FILENAME_BOOKX_CONDITIONS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $bookx_condition_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page']));
        }
        $bookx_condition_id = zen_db_prepare_input($_POST['mID']);

        $db->Execute("delete from " . TABLE_PRODUCT_BOOKX_CONDITIONS . "
                      where bookx_condition_id = '" . (int)$bookx_condition_id . "'");
        $db->Execute("delete from " . TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION . "
                      where bookx_condition_id = '" . (int)$bookx_condition_id . "'");

        if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
          $products = $db->Execute("select p.products_id
                                             from " . TABLE_PRODUCT_BOOKX_EXTRA . " p
                                             where p.bookx_condition_id = '" . (int)$bookx_condition_id . "'");

          while (!$products->EOF) {
             bookx_delete_product((int)$products->fields['products_id']);
            $products->MoveNext();
          }
        }

        zen_redirect(zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page']));
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<?php if ($editor_handler != '') include_once ($editor_handler); ?>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONDITIONS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $conditions_query_raw = 'select c.*, cd.condition_description from ' . TABLE_PRODUCT_BOOKX_CONDITIONS . ' c LEFT JOIN ' . TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION. ' cd ON cd.bookx_condition_id = c.bookx_condition_id AND cd.languages_id = ' . $_SESSION['languages_id'] . ' order by c.condition_sort_order, cd.condition_description';
  $conditions_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $conditions_query_raw, $conditions_query_numrows);
  $conditions = $db->Execute($conditions_query_raw);

  while (!$conditions->EOF) {

    if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $conditions->fields['bookx_condition_id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
	  $conditions_products = $db->Execute("select count(p.products_id) as products_count
                                             from " . TABLE_PRODUCT_BOOKX_EXTRA . " p
                                             where p.bookx_condition_id = '" . (int)$conditions->fields['bookx_condition_id'] . "'");


      $aInfo_array = array_merge($conditions->fields, $conditions_products->fields);
      $aInfo = new objectInfo($aInfo_array);
    }

    if (isset($aInfo) && is_object($aInfo) && ($conditions->fields['bookx_condition_id'] == $aInfo->bookx_condition_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $conditions->fields['bookx_condition_id'] . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $conditions->fields['bookx_condition_id'] . '&action=edit') . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $conditions->fields['condition_description']; ?></td>
                <td class="dataTableContent"><?php echo $conditions->fields['condition_sort_order']; ?></td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $conditions->fields['bookx_condition_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $conditions->fields['bookx_condition_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php if (isset($aInfo) && is_object($aInfo) && ($conditions->fields['bookx_condition_id'] == $aInfo->bookx_condition_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, zen_get_all_get_params(array('mID')) . 'mID=' . $conditions->fields['bookx_condition_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                </td>
              </tr>
<?php
    $conditions->MoveNext();
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $conditions_split->display_count($conditions_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CONDITIONS); ?></td>
                    <td class="smallText" align="right"><?php echo $conditions_split->display_links($conditions_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="3" class="smallText"><?php echo '<a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . /*$aInfo->bookx_condition_id .*/ '&action=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_CONDITIONS . '</b>');

      $contents = array('form' => zen_draw_form('conditions', FILENAME_BOOKX_CONDITIONS, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);

      $condition_description_inputs = '';
      $languages = zen_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_image = zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']);
      	$condition_description_inputs .= '<br>' . $language_image . '&nbsp;' . zen_draw_input_field('condition_description[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION, 'condition_description')); 
	  }
	  $contents[] = array('text' => '<br>' . TEXT_CONDITIONS_DESCRIPTION . $condition_description_inputs);
	  
      $contents[] = array('text' => '<br />' . TEXT_CONDITIONS_SORT_ORDER . '<br>' . zen_draw_input_field('condition_sort_order'));
      
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_CONDITIONS . '</b>');

      $contents = array('form' => zen_draw_form('conditions', FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_condition_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);

      $condition_description_inputs = '';
      $languages = zen_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_image = zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']);

		$condition_description_inputs .= '<br>' . $language_image . '&nbsp;' . zen_draw_input_field('condition_description[' . $languages[$i]['id'] . ']', bookx_get_condition_description($aInfo->bookx_condition_id, $languages[$i]['id']), zen_set_field_length(TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION, 'condition_description'));
		
      }
      $contents[] = array('text' => '<br>' . TEXT_CONDITIONS_DESCRIPTION . $condition_description_inputs);
      
      $contents[] = array('text' => '<br />' . TEXT_CONDITIONS_SORT_ORDER . '<br>' . zen_draw_input_field('condition_sort_order', $aInfo->condition_sort_order));
      
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_condition_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_CONDITIONS . '</b>');

      $contents = array('form' => zen_draw_form('conditions', FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $aInfo->bookx_condition_id));
      $contents[] = array('text' => sprintf(TEXT_DELETE_INTRO, $aInfo->condition_description));
      $contents[] = array('text' => '<br><b>' . $aInfo->condition_description . '</b>');

      if ($aInfo->products_count > 0) {
        $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_products') . ' ' . sprintf(TEXT_DELETE_PRODUCTS, $aInfo->condition_description));
        $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $aInfo->products_count, $aInfo->condition_description));
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_condition_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($aInfo) && is_object($aInfo)) {
        $heading[] = array('text' => '<b>' . $aInfo->condition_description . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_condition_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_BOOKX_CONDITIONS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_condition_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $aInfo->products_count);
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
