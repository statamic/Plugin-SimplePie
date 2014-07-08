<?php

require_once('lib/simplepie_1.3.mini.php');

class Plugin_simplepie extends Plugin
{

  var $meta = array(
  'name'       => 'Simple Pie',
  'version'    => '1.0.1',
  'author'     => 'Statamic',
  'author_url' => 'http://statamic.com'
  );

  public function index()
  {
    $url            = $this->fetchParam('url', null);
    $order_by_date  = $this->fetchParam('order_by_date', true, false, true);
    $offset         = $this->fetchParam('offset', 0);
    $cache          = $this->fetchParam('cache', false, false, true);
    $timeout        = $this->fetchParam('timeout', 10);
    
    $count          = $this->fetchParam('count', false);
    $limit          = $this->fetchParam('limit', 10);
    $limit = $limit == 'no' ? 0 : $limit;

    if ($count && $this->fetchParam('limit', false) === false) {
      $limit = $count; # backwards compatibility
    }

    $feed = new SimplePie();

    $cache_folder = '_cache/_add-ons/simplepie';
    if ( ! Folder::exists($cache_folder)) {
      Folder::make($cache_folder);
    }
    $feed->set_cache_location($cache_folder);
    $feed->enable_cache($cache);

    $feed->set_feed_url($url);
    $feed->enable_order_by_date($order_by_date);

    $success = $feed->init();
    $feed->handle_content_type();

    if ( ! $feed->error() && $success) {

      $loop_count = 0;
      $output = '';

      foreach($feed->get_items($offset) as $item) {
        $data = array();
        $data['title']        = $item->get_title();
        $data['permalink']    = $item->get_permalink();
        $data['date']         = $item->get_date(Config::getDateFormat());
        $data['updated_date'] = $item->get_updated_date(Config::getDateFormat());
        $data['author']       = $item->get_author();
        $data['category']     = $item->get_category();
        $data['description']  = $item->get_description();
        $data['content']      = $item->get_content();

        $loop_count ++;
        $output .= Parse::template($this->content, $data);

        if ($loop_count >= $limit) break;
      }

      return $output;
    }

    return '';
  }

  public function feed()
  {
    return $this->index();
  }

}
