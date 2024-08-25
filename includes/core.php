<?php

class CodeCruze_Core
{

	public static function get_searchable_checkbox()
	{
		$checkboxes = [
			'Products' => [
				'product_name' => 'Product Name',
				'product_shdsc' => 'Product Short Description',
				'product_dsc' => 'Product Description',
				'product_price' => 'Product Price',
				'product_cat' => 'Product Categories',
				'product_tag' => 'Product Tags',
			],
			'Orders' => [
				'order_billing' => 'Order Billing',
				'order_items' => 'Order Items',
				'order_amount' => 'Order Amount',
				'order_notes' => 'Order Notes',
			],
			'Customer' => ['Customer Billing']

		];

		$response = '';
		$codecruze_settings = CodeCruze_Core::search_include_options();

		foreach ($checkboxes as $mainLabel => $subCheckboxes) {
			$response .= "<hr style='text-align:center;margin-left:0'><label class='codecruze-settings-checkbox main-checkbox'>
		<input type='checkbox' class='main-checkbox' id='main-$mainLabel' /> <b>$mainLabel</b>
		</label> <hr style='width:13%;text-align:left;margin-left:0'>";

			foreach ($subCheckboxes as $type => $label) {
				$selected = ($codecruze_settings !== false && in_array($type, $codecruze_settings)) ? 'checked' : '';
				$response .= "<label class='codecruze-settings-checkbox sub-checkbox'>
			<input type='checkbox' value='$type' name='search_include_options[]' id='$type' class='sub-checkbox' data-main='$mainLabel' $selected /> $label
			</label> <br>";
			}
		}
		$response .= "<script>
	document.addEventListener('DOMContentLoaded', function () {
		var mainCheckboxes = document.querySelectorAll('.main-checkbox');
		var subCheckboxes = document.querySelectorAll('.sub-checkbox');

		mainCheckboxes.forEach(function (mainCheckbox) {
			mainCheckbox.addEventListener('change', function () {
				var mainLabel = mainCheckbox.id.replace('main-', '');
				var relatedSubCheckboxes = document.querySelectorAll('.sub-checkbox[data-main=\"' + mainLabel + '\"]');

				relatedSubCheckboxes.forEach(function (subCheckbox) {
					subCheckbox.checked = mainCheckbox.checked;
				});

				// Check if all sub-checkboxes are checked
				mainCheckbox.checked = Array.from(relatedSubCheckboxes).every(function (checkbox) {
					return checkbox.checked;
				});
			});

			// Check the state of the main checkbox on page load
			var mainLabel = mainCheckbox.id.replace('main-', '');
			var relatedSubCheckboxes = document.querySelectorAll('.sub-checkbox[data-main=\"' + mainLabel + '\"]');
			mainCheckbox.checked = Array.from(relatedSubCheckboxes).every(function (checkbox) {
				return checkbox.checked;
			});
		});

		subCheckboxes.forEach(function (subCheckbox) {
			subCheckbox.addEventListener('change', function () {
				var mainLabel = subCheckbox.dataset.main;
				var relatedSubCheckboxes = document.querySelectorAll('.sub-checkbox[data-main=\"' + mainLabel + '\"]');
				var mainCheckbox = document.getElementById('main-' + mainLabel);

				// Check if all sub-checkboxes are checked
				mainCheckbox.checked = Array.from(relatedSubCheckboxes).every(function (checkbox) {
					return checkbox.checked;
				});
			});
		});
	});
  </script>";

		return $response;

	}


	public static function codecruze_get_settings()
	{
		$codecruze_setting = get_option('codecruze_setting');
		if (empty($codecruze_setting)) {
			return false;
		}

		return unserialize($codecruze_setting);
	}

	public static function search_include_options()
	{
		$codecruze_setting = CodeCruze_Core::codecruze_get_settings();
		if (empty($codecruze_setting['search_include_options'])) {
			return false;
		}
		return $codecruze_setting['search_include_options'];
	}

	public static function get_search_content()
	{
		global $wpdb, $wp_admin_bar;
		$final_response = array();

		$search_content_functions = [
			'get_product_name',
			'get_product_shdsc',
			'get_product_dsc',
			'get_product_price',
			'get_product_cat',
			'get_product_tag',
			'get_billing_order',
			'get_items_order',
			'get_order_amount',
			'get_all_order_notes',
			'get_customer_billing',

		];

		foreach ($search_content_functions as $function) {
			$result = call_user_func(['CodeCruze_Core', $function]);

			if (!empty($result)) {
				$final_response = array_merge($final_response, $result);
			}
		}
		$unique_final_response = [];
		$unique_ids = [];
		$quick_search_status = get_option('codecruze_quick_search_status');

		foreach ($final_response as $item) {
			$id = $item['ID'];

			if (!in_array($id, $unique_ids)) {
				$unique_final_response[] = $item;
				$unique_ids[] = $id;
			}
		}

		if ($quick_search_status == 'true') {

			return $unique_final_response;
		} else {
			return $final_response;
		}
	}

	public static function get_product_name()
	{
		global $wpdb;

		$all_products = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('product_name', $codecruze_setting)) {
			return array();
		}

		$product_query = "SELECT p.ID, p.post_title, p.post_type
                  FROM {$wpdb->posts} p
                  WHERE p.post_type IN ('product', 'product_variation') AND p.post_status = 'publish'
                  GROUP BY p.ID";

		$product_results = $wpdb->get_results($product_query, ARRAY_A);

		foreach ($product_results as $result) {
			$product = array();
			$product['type'] = $result['post_type'];
			$product['ID'] = $result['ID'];
			if ($quick_search_status == 'true') {
				$product['title'] = 'Product #' . $result['ID'] . '<div style="display:none;">' . $result['post_title'] . '</div>';
				$product['category'] = '<div style="display:none;">#' . $result['ID'] . ' Product</div>';
			} else {
				$product['title'] = $result['post_title'];
				$product['category'] = 'Product #' . $result['ID'];
				$product['description'] = 'Name';
				$meta = get_post_meta($result['ID']);
				$_price = isset($meta['_regular_price'][0]) ? $meta['_regular_price'][0] : null;
				$sale_price = isset($meta['_sale_price'][0]) ? $meta['_sale_price'][0] : null;
				$currency = get_option('woocommerce_currency');
				if ($sale_price) {
					$product['price'] = 'Regular: <s>' . $_price . ' ' . $currency . '</s> / Sale: ' . $sale_price . ' ' . $currency;
				} elseif ($_price) {
					$product['price'] = 'Regular: ' . $_price . ' ' . $currency;
				}
			}
			$product['url'] = 'post.php?post=' . $result['ID'] . '&action=edit" target="_blank';

			array_push($all_products, $product);
		}

		return $all_products;
	}
	public static function get_product_dsc()
	{
		global $wpdb;

		$all_products = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('product_dsc', $codecruze_setting)) {
			return array();
		}

		$product_query = "SELECT p.ID, p.post_content, p.post_type
                  FROM {$wpdb->posts} p
                  WHERE p.post_type IN ('product', 'product_variation') AND p.post_status = 'publish'
                  GROUP BY p.ID";

		$product_results = $wpdb->get_results($product_query, ARRAY_A);

		foreach ($product_results as $result) {
			$product = array();
			$product['type'] = $result['post_type'];
			$product['ID'] = $result['ID'];
			$words = explode(' ', $result['post_content']);
			$truncated_description = '';
			foreach ($words as $word) {
				if (strlen($truncated_description . $word) <= 70) {
					$truncated_description .= $word . ' ';
				} else {
					break;
				}
			}
			if ($quick_search_status == 'true') {
				$product['title'] = 'Product #' . $result['ID'] . '<div style="display:none;">' . $result['post_content'] . '</div>';
				$product['category'] = '<div style="display:none;">#' . $result['ID'] . ' Product</div>';
			} else {
				$product['title'] = rtrim($truncated_description) . (strlen($truncated_description) < strlen($result['post_content']) ? '... See more' : '');
				$product['description'] = 'Description';
				$product['category'] = 'Product #' . $result['ID'];
			}
			$product['url'] = 'post.php?post=' . $result['ID'] . '&action=edit" target="_blank';
			array_push($all_products, $product);
		}

		return $all_products;
	}
	public static function get_product_shdsc()
	{
		global $wpdb;

		$all_products = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('product_shdsc', $codecruze_setting)) {
			return array();
		}

		$product_query = "SELECT p.ID, p.post_excerpt, p.post_type
                  FROM {$wpdb->posts} p
                  WHERE p.post_type IN ('product', 'product_variation') AND p.post_status = 'publish'
                  GROUP BY p.ID";

		$product_results = $wpdb->get_results($product_query, ARRAY_A);

		foreach ($product_results as $result) {
			$product = array();
			$product['type'] = $result['post_type'];
			$product['ID'] = $result['ID'];
			$words = explode(' ', $result['post_excerpt']);
			$truncated_description = '';
			foreach ($words as $word) {
				if (strlen($truncated_description . $word) <= 70) {
					$truncated_description .= $word . ' ';
				} else {
					break;
				}
			}
			if ($quick_search_status == 'true') {
				$product['title'] = 'Product #' . $result['ID'] . '<div style="display:none;">' . $result['post_excerpt'] . '</div>';
				$product['category'] = '<div style="display:none;">#' . $result['ID'] . ' Product</div>';
			} else {
				$product['title'] = rtrim($truncated_description) . (strlen($truncated_description) < strlen($result['post_excerpt']) ? '... See more' : '');
				$product['description'] = 'Short Description';
				$product['category'] = 'Product #' . $result['ID'];
			}
			$product['url'] = 'post.php?post=' . $result['ID'] . '&action=edit" target="_blank';
			array_push($all_products, $product);
		}

		return $all_products;
	}

	public static function get_product_price()
	{
		global $wpdb;

		$all_products = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('product_price', $codecruze_setting)) {
			return array();
		}

		$product_query = "SELECT p.ID, p.post_type
                  FROM {$wpdb->posts} p
                  WHERE p.post_type IN ('product') AND p.post_status = 'publish'
                  GROUP BY p.ID";

		$product_results = $wpdb->get_results($product_query, ARRAY_A);

		foreach ($product_results as $result) {
			$product = array();
			$product['type'] = $result['post_type'];
			$product['ID'] = $result['ID'];
			$meta = get_post_meta($result['ID']);
			$_price = isset($meta['_regular_price'][0]) ? $meta['_regular_price'][0] : null;
			$sale_price = isset($meta['_sale_price'][0]) ? $meta['_sale_price'][0] : null;
			$currency = get_option('woocommerce_currency');
			if ($quick_search_status == 'true') {
				if ($sale_price) {
					$product['title'] = 'Product #' . $result['ID'] . '<div style="display:none;">' . 'Regular: <s>' . $_price . ' ' . $currency . '</s> / Sale: ' . $sale_price . ' ' . $currency . '</div>';
				} elseif ($_price) {
					$product['title'] = 'Product #' . $result['ID'] . '<div style="display:none;">' . 'Regular: ' . $_price . ' ' . $currency . '</div>';
				}
				$product['category'] = '<div style="display:none;">#' . $result['ID'] . ' Product</div>';
			} else {
				if ($sale_price) {
					$product['title'] = 'Regular: <s>' . $_price . ' ' . $currency . '</s> / Sale: ' . $sale_price . ' ' . $currency;
				} elseif ($_price) {
					$product['title'] = 'Regular: ' . $_price . ' ' . $currency;
				}
				$product['description'] = 'Price';
				$product['category'] = 'Product #' . $result['ID'];
			}
			$product['url'] = 'post.php?post=' . $result['ID'] . '&action=edit" target="_blank';
			array_push($all_products, $product);
		}

		return $all_products;
	}

	public static function get_product_cat()
	{
		global $wpdb;

		$all_categories = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('product_cat', $codecruze_setting)) {
			return array();
		}

		$category_query = "SELECT tt.taxonomy, t.term_id, t.name as category
		FROM {$wpdb->terms} t
		JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
		WHERE tt.taxonomy = 'product_cat'
		GROUP BY t.term_id";

		$category_results = $wpdb->get_results($category_query, ARRAY_A);

		foreach ($category_results as $result) {
			$category = array();
			$category['type'] = $result['taxonomy'];
			$category['ID'] = $result['term_id'];
			if ($quick_search_status == 'true') {
				$category['title'] = 'Product Category #' . $result['term_id'] . '<div style="display:none;">' . $result['category'] . '</div>';
				$category['category'] = '<div style="display:none;">Product Category #' . $result['term_id'] . '</div>';

			} else {
				$category['title'] = $result['category'];
				$category['category'] = 'Product Category #' . $result['term_id'];
			}
			$category['url'] = 'edit-tags.php?action=edit&taxonomy=product_cat&tag_ID=' . $result['term_id'] . '" target="_blank';
			array_push($all_categories, $category);
		}

		return $all_categories;
	}
	public static function get_product_tag()
	{
		global $wpdb;

		$all_tags = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('product_tag', $codecruze_setting)) {
			return array();
		}

		$tag_query = "SELECT tt.taxonomy, t.term_id, t.name as tag
		FROM {$wpdb->terms} t
		JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
		WHERE tt.taxonomy = 'product_tag'
		GROUP BY t.term_id";

		$tag_results = $wpdb->get_results($tag_query, ARRAY_A);

		foreach ($tag_results as $result) {
			$tag = array();
			$tag['type'] = $result['taxonomy'];
			$tag['ID'] = $result['term_id'];
			if ($quick_search_status == 'true') {
				$tag['title'] = 'Product Tag #' . $result['term_id'] . '<div style="display:none;">' . $result['tag'] . '</div>';
				$tag['category'] = '<div style="display:none;">Product Tag #' . $result['term_id'] . '</div>';
			} else {
				$tag['title'] = $result['tag'];
				$tag['category'] = 'Product Tag #' . $result['term_id'];
			}
			$tag['url'] = 'edit-tags.php?action=edit&taxonomy=product_tag&tag_ID=' . $result['term_id'] . '" target="_blank';
			array_push($all_tags, $tag);
		}

		return $all_tags;
	}
	public static function get_customer_billing()
	{
		global $wpdb;

		$all_customers = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('customer', $codecruze_setting)) {
			return array();
		}

		$customer_query = "SELECT u.ID, u.user_nicename, wccl.first_name, wccl.last_name, wccl.email, wccl.state, o.user_id,
                      MAX(CASE WHEN o.meta_key = 'billing_phone' THEN o.meta_value END) AS billing_phone,
                      MAX(CASE WHEN o.meta_key = 'billing_address_1' THEN o.meta_value END) AS billing_address
                     FROM {$wpdb->users} u
                     INNER JOIN {$wpdb->prefix}wc_customer_lookup wccl ON u.ID = wccl.user_id
                     INNER JOIN {$wpdb->usermeta} o ON u.ID = o.user_id
					 WHERE o.meta_key IN ('billing_phone', 'billing_address_1')
                     GROUP BY u.ID
                                       ";

		$customer_results = $wpdb->get_results($customer_query, ARRAY_A);

		foreach ($customer_results as $result) {
			$customer = array();
			$customer['type'] = 'customer';
			$customer['ID'] = $result['ID'];
			$wc_countries = new WC_Countries();
			$all_states = $wc_countries->get_states();
			if ($quick_search_status == 'true') {
				$customer['title'] = 'Customer #' . $result['ID'] . '<p style="display:none;">' . $result['user_nicename'] . $result['first_name'] . ' ' . $result['last_name'] . ', ' . $result['email'] . ', ' . $result['billing_phone'] . ', ' . (isset($all_states['EG'][$result['state']]) ? $all_states['EG'][$result['state']] : $result['state']) . ', ' . $result['billing_address'] . '</p>';
				$customer['category'] = '<div style="display:none;">Customer #' . $result['ID'] . '</div>';
			} else {
				$customer['title'] = '<p style="font-weight:bold; color:green;">' . $result['user_nicename'] . '</p>' . $result['first_name'] . ' ' . $result['last_name'] . ', ' . $result['email'] . ', ' . $result['billing_phone'] . ', ' . (isset($all_states['EG'][$result['state']]) ? $all_states['EG'][$result['state']] : $result['state']) . ', ' . $result['billing_address'];
				$customer['category'] = 'Customer #' . $result['ID'];
				$customer['description'] = 'Customer Billing';
			}
			$customer['url'] = 'user-edit.php?user_id=' . $result['ID'] . '" target="_blank';
			array_push($all_customers, $customer);
		}

		return $all_customers;
	}

	public static function get_billing_order()
	{
		global $wpdb;
		$all_orders = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('order_billing', $codecruze_setting)) {
			return array();
		}

		$order_query = $wpdb->prepare(
			"SELECT oa.*, wo.total_amount, wo.status
			FROM {$wpdb->prefix}wc_order_addresses AS oa
			JOIN {$wpdb->prefix}wc_orders AS wo ON oa.order_id = wo.id
			WHERE oa.address_type = 'billing'"
		);

		$order_content = $wpdb->get_results($order_query, ARRAY_A);

		foreach ($order_content as $resultKey => $content) {
			$wc_countries = new WC_Countries();
			$all_states = $wc_countries->get_states();
			$orders = array();
			$orders['type'] = 'wc_order';
			$orders['ID'] = $content['order_id'];
			if ($quick_search_status == 'true') {
				$orders['title'] = 'Order #' . $content['order_id'] . ' ' . strtoupper(str_replace('wc-', '', $content['status'])) . '<p style="display:none;">' . $content['first_name'] . ' ' . $content['last_name'] . ', ' . $content['email'] . ', ' . $content['phone'] . ', ' . $all_states['EG'][$content['state']] . ', ' . $content['address_1'] . '</p>';
				$orders['category'] = '<div style="display:none;">Order #' . $content['order_id'] . '</div>';
			} else {
				$orders['title'] = $content['first_name'] . ' ' . $content['last_name'] . ', ' . $content['email'] . ', ' . $content['phone'] . ', ' . $all_states['EG'][$content['state']] . ', ' . $content['address_1'];
				$orders['category'] = 'Order #' . $content['order_id'] . '<br>' . strtoupper(str_replace('wc-', '', $content['status']));
				$orders['description'] = 'Order Billing';
				$currency = get_option('woocommerce_currency');
				$orders['price'] = '<p style="text-align:right;">Total</p><hr>' . number_format((float) $content['total_amount'], 2, '.', '') . ' ' . $currency;
			}
			$orders['url'] = 'admin.php?page=wc-orders&action=edit&id=' . $content['order_id'] . '" target="_blank';

			array_push($all_orders, $orders);
		}

		return $all_orders;
	}

	public static function get_items_order()
	{
		global $wpdb, $woocommerce;
		$all_orders = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('order_items', $codecruze_setting)) {
			return array();
		}

		// Adjust the query to fetch orders from custom tables
		$order_query = $wpdb->prepare(
			"SELECT oi.order_item_id, oi.order_id, om.meta_value, os.status, os.total_amount
			FROM {$wpdb->prefix}woocommerce_order_items AS oi
			JOIN {$wpdb->prefix}wc_orders AS os ON oi.order_id = os.id
			JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON oi.order_item_id = om.order_item_id
			WHERE om.meta_key = 'Items';"
		);

		$order_content = $wpdb->get_results($order_query, ARRAY_A);

		foreach ($order_content as $resultKey => $content) {
			$orders = array();
			$orders['type'] = 'wc_order';
			$orders['ID'] = $content['order_id'];
			if ($quick_search_status == 'true') {
				$orders['title'] = $content['meta_value'];
				$orders['title'] = 'Order #' . $content['order_id'] . ' ' . strtoupper(str_replace('wc-', '', $content['status'])) . '<p style="display:none;">' . $content['meta_value'] . '</p>';
				$orders['category'] = '<div style="display:none;">Order #' . $content['order_id'] . '</div>';
			} else {
				$orders['title'] = $content['meta_value'];
				$orders['category'] = 'Order #' . $content['order_id'] . '<br>' . strtoupper(str_replace('wc-', '', $content['status']));
				$currency = get_option('woocommerce_currency');
				$orders['price'] = 'Total<hr>' . number_format((float) $content['total_amount'], 2, '.', '') . ' ' . $currency;
				$orders['description'] = 'Order Items';
			}
			$orders['url'] = 'admin.php?page=wc-orders&action=edit&id=' . $content['order_id'] . '" target="_blank';

			array_push($all_orders, $orders);
		}

		return $all_orders;
	}

	public static function get_order_amount()
	{
		global $wpdb, $woocommerce;
		$all_orders = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false || !in_array('order_amount', $codecruze_setting)) {
			return array();
		}

		// Adjust the query to fetch orders from custom tables
		$order_query = $wpdb->prepare(
			"SELECT ID, total_amount, status 
			FROM {$wpdb->prefix}wc_orders"
		);

		$order_content = $wpdb->get_results($order_query, ARRAY_A);

		foreach ($order_content as $resultKey => $content) {
			$orders = array();
			$orders['type'] = 'wc_order';
			$orders['ID'] = $content['ID'];
			$currency = get_option('woocommerce_currency');
			if ($quick_search_status == 'true') {
				$orders['title'] = 'Order #' . $content['ID'] . ' ' . strtoupper(str_replace('wc-', '', $content['status'])) . '<p style="display:none;">Total' . number_format((float) $content['total_amount'], 2, '.', '') . ' ' . $currency . '</p>';
				$orders['category'] = '<div style="display:none;">Order #' . $content['ID'] . '</div>';
			} else {
				$orders['title'] = 'Total<hr>' . number_format((float) $content['total_amount'], 2, '.', '') . ' ' . $currency;
				$orders['category'] = 'Order #' . $content['ID'] . '<br>' . strtoupper(str_replace('wc-', '', $content['status']));
				$orders['description'] = 'Order Amount';
			}
			$orders['url'] = 'admin.php?page=wc-orders&action=edit&id=' . $content['ID'] . '" target="_blank';

			array_push($all_orders, $orders);
		}

		return $all_orders;
	}

	public static function get_all_order_notes()
	{
		$order_note_results = array();
		$codecruze_setting = CodeCruze_Core::search_include_options();
		$quick_search_status = get_option('codecruze_quick_search_status');

		if ($codecruze_setting == false) {
			return array();
		}
		if (!in_array('order_notes', $codecruze_setting)) {
			return $order_note_results;
		}

		global $wpdb;

		$order_query = $wpdb->prepare(
			"SELECT o.* , wo.status, wo.id
			 FROM {$wpdb->prefix}comments AS o
			 JOIN {$wpdb->prefix}wc_orders AS wo ON o.comment_post_ID = wo.id
			 WHERE comment_type = 'order_note'"
		);

		$order_notes = $wpdb->get_results($order_query, ARRAY_A);

		foreach ($order_notes as $key => $value) {
			$comment_temp = array();
			$comment_temp['ID'] = $value['comment_post_ID'];
			$comment_temp['type'] = $value['comment_type'];
			$words = explode(' ', $value['comment_content']);
			$truncated_description = '';
			foreach ($words as $word) {
				if (strlen($truncated_description . $word) <= 70) {
					$truncated_description .= $word . ' ';
				} else {
					break;
				}
			}
			if ($quick_search_status == 'true') {
				$comment_temp['title'] = 'Order #' . $value['comment_post_ID'] . ' ' . strtoupper(str_replace('wc-', '', $value['status'])) . '<div style="display:none;">' . $value['comment_content'] . '</div>';
				$comment_temp['category'] = '<div style="display:none;">#' . $value['comment_post_ID'] . ' Order</div>';
			} else {
				$comment_temp['title'] = rtrim($truncated_description) . (strlen($truncated_description) < strlen($value['comment_content']) ? '... See more' : '');
				$comment_temp['price'] = $value['comment_author'];
				$comment_temp['category'] = 'Order #' . $value['comment_post_ID'] . '<br>' . strtoupper(str_replace('wc-', '', $value['status']));
				$comment_temp['description'] = 'Order Notes';
			}
			$comment_temp['url'] = 'admin.php?page=wc-orders&action=edit&id=' . $value['comment_post_ID'] . '" target="_blank';
			;
			array_push($order_note_results, $comment_temp);
		}

		return $order_note_results;
	}
	public static function codecruze_save_settings($data)
	{
		if (empty($data['search_include_options']) && empty($data['submit'])) {
			return false;
		}
		if (empty($data['search_include_options']) && !empty($data['submit'])) {
			delete_option('codecruze_setting');
			return true;
		}
		$settings['search_include_options'] = $data['search_include_options'];
		update_option('codecruze_setting', serialize($settings));
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php _e('Global Search settings saved', CODECRUZE_SEARCH_NAME); ?>
			</p>
		</div>
		<?php
	}

	public static function codecruze_admin_notice()
	{
		return get_option('codecruze_admin_notice');
	}

	public static function codecruze_save_admin_notice()
	{
		update_option('codecruze_admin_notice', 1);
	}

	public static function codecruze_update_notice()
	{
		return get_option('codecruze_update_notice');
	}

	public static function codecruze_save_update_notice()
	{
		update_option('codecruze_update_notice', 1);
	}
}
