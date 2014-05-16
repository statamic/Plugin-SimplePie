<?php
require_once('lib/simplepie_1.3.mini.php');
class Plugin_simplepie extends Plugin {

  var $meta = array(
  'name'       => 'Simple Pie',
  'version'    => '1.0',
  'author'     => 'Statamic',
  'author_url' => 'http://statamic.com'
  );

  public function index() {
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

    $feed->set_cache_location("_cache");
    $feed->enable_cache($cache);

    $feed->set_feed_url($url);
    $feed->enable_order_by_date($order_by_date);

    $success = $feed->init();
    $feed->handle_content_type();

    if ($feed->error()) {
    } else {
      if ($success) {
        $loop_count = 0;
        $output = "";
        $parser = new Lex_Parser();
        $parser->scope_glue(':');
        $parser->cumulative_noparse(true);

        $debug = array();
        foreach($feed->get_items($offset) as $item) {
          $arr = array();
          $arr['title']        = $item->get_title();
          $arr['permalink']    = $item->get_permalink();
          $arr['date']         = $item->get_date(Config::getDateFormat());
          $arr['updated_date'] = $item->get_updated_date(Config::getDateFormat());
          $arr['author']       = $item->get_author();
          $arr['category']     = $item->get_category();
          $arr['description']  = $item->get_description();
          $arr['content']      = $item->get_content();

          $loop_count ++;
          $output .= $parser->parse($this->content, $arr);
          if ($loop_count >= $limit) {
            break;
          }
        }
        return $output;
      }
    }
    return '';
  }

  public function feed() {
    return $this->index();
  }

}
