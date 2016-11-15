<?php

$plugin_info = array(
  'pi_name' => 'Cat2',
  'pi_version' => '2.0.1',
  'pi_author' => 'Mark Croxton, Hallmark Design',
  'pi_author_url' => 'http://hallmark-design.co.uk',
  'pi_description' => 'Convert between category name, category id and category url title',
  'pi_usage' => Cat2::usage()
);

class Cat2 {
	
	public $return_data = '';
	public $category_url_title;
	public $category_id;
	public $category_name;
	public $category_group;
	public $prefix;
	public $site;
	private $_debug;
	
	/** 
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function Cat2() 
	{
		$this->site = ee()->config->item('site_id');
		
		// register parameters
		$this->category_url_title = strtolower(ee()->TMPL->fetch_param('category_url_title', ''));
		$this->category_name = strtolower(ee()->TMPL->fetch_param('category_name', ''));
		$this->category_id = preg_replace("/[^0-9]/", '', ee()->TMPL->fetch_param('category_id', NULL));
		$this->category_group = ee()->TMPL->fetch_param('category_group', '');
		$this->_debug = (bool) preg_match('/1|on|yes|y/i', ee()->TMPL->fetch_param('debug'));	

		// add a prefix?
		$this->prefix = ee()->TMPL->fetch_param('prefix', '');
		if ( ! empty($this->prefix))
		{
			$this->prefix = $this->prefix . ":";
		}
		
		// set up cache
		if ( ! isset(ee()->session->cache[__CLASS__]))
        {
            ee()->session->cache[__CLASS__] = array();
        }
	}
	
	/** 
	 * exp:cat2:id
	 *
	 * @access public
	 * @return string
	 */
	function id() 
	{
		if (empty($this->category_url_title) && empty($this->category_name))
		{
			// parameter required, fail gracefully
			if ($this->_debug)
			{
				show_error(__CLASS__.' error: one of the following parameters is required: category_url_title OR category_name.');
			}
			return;
		}
		
		if (!empty($this->category_url_title))
		{
			$key 	= "cat_url_title";
			$value 	= $this->category_url_title;
		}
		else
		{
			$key 	= "cat_name";
			$value	 = $this->category_name;
		}
		
		return $this->cat_query('cat_id', $key, $value);
	}
		
	/** 
	 * exp:cat2:name
	 *
	 * @access public
	 * @return string
	 */
	function name() 
	{
		if (empty($this->category_url_title) && empty($this->category_id))
		{
			// parameter required, fail gracefully
			if ($this->_debug)
			{
				show_error(__CLASS__.' error: one of the following parameters is required: category_url_title OR category_id.');
			}
			return;
		}
		
		if ( ! empty($this->category_url_title))
		{
			$key 	= "cat_url_title";
			$value 	=  $this->category_url_title;
		}
		else
		{
			$key 	= "cat_id";
			$value	 = $this->category_id;
		}
		
		return $this->cat_query('cat_name', $key, $value);
	}
	
	/** 
	 * exp:cat2:url_title
	 *
	 * @access public
	 * @return string
	 */
	function url_title() 
	{
		if (empty($this->category_name) && empty($this->category_id))
		{
			// parameter required, fail gracefully
			if ($this->_debug)
			{
				show_error(__CLASS__.' error: one of the following parameters is required: category_name OR category_id.');
			}
			return;
		}
		
		if ( ! empty($this->category_name))
		{
			$key 	= "cat_name";
			$value 	=  $this->category_name;
		}
		else
		{
			$key 	= "cat_id";
			$value	 = $this->category_id;
		}
		
		return $this->cat_query('cat_url_title', $key, $value);
	}
	
	/** 
	 * The main query
	 *
	 * @access public
	 * @param string $col 	the column we want to get
	 * @param string $key  	the column we're searching
	 * @param string $value the column we're searching
	 * @return string
	 */
	protected function cat_query($col, $key, $value)
	{
		if ( ! isset(ee()->session->cache[__CLASS__][$col][$value]) )
		{
			// query
			ee()->db->select($col);
			ee()->db->from('exp_categories');
			ee()->db->where('site_id', $this->site);
		
			if ($key == 'cat_id')
			{
				ee()->db->where($key, $value);
			}
			else
			{
				ee()->db->where("LOWER({$key})", $value);
			}
			
			if ( ! empty($this->category_group))
			{
				if (strpos($this->category_group, '|') !== false)
				{
					ee()->db->where_in('group_id', explode('|', $this->category_group));
				}
				else
				{
					ee()->db->where('group_id', $this->category_group);
				}
			}
			
			// run the query
			$results = ee()->db->get();
			
			if ($results->num_rows() > 0) 
			{
				ee()->session->cache[__CLASS__][$col][$value] = (string) $results->row($col);
			}
			else
			{
				// fail gracefully
				ee()->session->cache[__CLASS__][$col][$value] = '';
				
				if ($this->_debug)
				{
					show_error(__CLASS__.' error: category not found.');
				}
			}
		}
		
		// is this a tag pair?
		$tagdata = ee()->TMPL->tagdata;
	
		if ( ! empty($tagdata))
		{
			$data = array(
				$this->prefix.(str_replace('cat', 'category', $col)) => ee()->session->cache[__CLASS__][$col][$value]
			);
			
			return ee()->TMPL->parse_variables_row($tagdata, $data);
		}
		else
		{
			// output direct
			return ee()->session->cache[__CLASS__][$col][$value];
		}
	}

	// usage instructions
	public static function usage()
	{
  		ob_start();
?>
-------------------
HOW TO USE
-------------------

Convert between category name, category id and category url title.
Query results are cached, so you can use the same tag multiple times
in your template without additional overhead.

Tags:
{exp:cat2:id}
{exp:cat2:name}
{exp:cat2:url_title}

Required Parameters:
category_url_title=
category_name=
category_id=
One of these must be present in order to find the correct category.

Optional Parameters:
category_group=
Filter category results by a specific category group ID, or multiple category group IDs.
Examples:
category_group="2"
category_group="5|6"

prefix=
Output error messages if tag is used incorrectly. Can be "yes" or "no" (default is "no").

debug=
Output error messages if tag is used incorrectly. Can be "yes" or "no" (default is "no").

Examples:

category_id from category_url_title:
{exp:cat2:id category_url_title="my_category"}

category_id from category_name:
{exp:cat2:id category_name="my category"}

category_name from category_id:
{exp:cat2:name category_id="25"}

category_name from category_url_title:
{exp:cat2:name category_url_title="my_category"}

category_url_title from category_id:
{exp:cat2:url_title category_id="25"}

category_url_title from category_name:
{exp:cat2:url_title category_name="my category"}

Can also be used as a tag pair, e.g.:

{exp:cat2:id category_url_title="my_category" parse="inward"}
	{category_id}
{/exp:cat2:id}

{exp:cat2:name category_id="25" parse="inward"}
	{category_name}
{/exp:cat2:name}

{exp:cat2:url_title category_id="25" parse="inward"}
	{category_url_title}
{/exp:cat2:url_title}

Use the prefix parameter to namespace variables:

{exp:cat2:id category_url_title="my_category" prefix="cat2" parse="inward"}
	{cat2:category_id}
{/exp:cat2:id}

	<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
