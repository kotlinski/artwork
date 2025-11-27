<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

class News extends CI_Controller {
	public $form_validation;
    public $session;
    public $news_model;
    public $images_model;

    public function __construct()
    {
        parent::__construct();
		$this->load->library('session');
		$this->load->model('News_model', 'news_model');
        $this->load->model('Images_model', 'images_model');
    }

    public function index()
    {
		$news = $this->news_model->get_news();

		foreach ($news as $key => $news_item) {
			$raw_text = $news_item['text'];
			$text = $this->prepareText($news_item);
			$news[$key]['text'] = $this->makelink($text);
			$news[$key]['text_raw'] = $raw_text;
		}
		$data['news'] = $news;
		$data['images'] = $this->images_model->get_images();
		$data['title'] = 'News';
        $data['menu_item'] = 'news';
        $this->load->view('templates/header', $data);
        $this->load->view('news/index', $data);
        $this->load->view('templates/footer');
    }

	public function prepareText($news_item)
	{

		$after = nl2br($news_item['text']);

		$text = "";

		while (strpos($after, '???') !== FALSE && strpos($after, '!!!') !== FALSE) {
			$start_pos = strpos($after, '???');
			$end_pos = strpos($after, '!!!');
			if ($start_pos !== FALSE || $end_pos !== FALSE) {
				$needle = substr($after, $start_pos + 3, ($end_pos - 3) - $start_pos);
				$image = $this->images_model->get_image($needle);
				if ($image) {
                    $image = '<img class="newsImg" src="' . base_url('/konst/medium/' . $image->file_name) . '" />';
                } else {
					$image = "";
				}
				$before = substr($after, 0, $start_pos);
				$after = substr($after, ($end_pos + 3));
				$text .= $before . $image;
			}
		}

		$text .= $after;

		return $text;

	}

	public function view($slug)
    {
		$news_item = $this->news_model->get_news($slug);
		if($news_item){
			$news_item_raw = $news_item;

			$text = $this->prepareText($news_item);

			$news_item['text'] = $this->makelink($text);
			$news_item['text_raw'] = $news_item_raw;
			$data['news_item'] = $news_item;


			$data['title'] = 'News';
			$data['menu_item'] = 'news';

			if (empty($data['news_item']))
			{
				show_404();
			}

			$data['title'] = $data['news_item']['title'];
			$data['images'] = $this->images_model->get_images();

			$this->load->view('templates/header', $data);
			$this->load->view('news/view', $data);
			$this->load->view('templates/footer');
		}
		else {
			$this->index();
		}
    }

    public function create()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $data['title'] = 'Create a news item';
        $data['menu_item'] = 'news';

        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('text', 'text', 'required');

        if ($this->form_validation->run() === FALSE)
        {
            $this->load->view('templates/header', $data);
            $this->load->view('pages/create');
            $this->load->view('templates/footer');

        }
        else
        {
            $this->news_model->set_news();
            $this->load->view('templates/header', $data);
            $this->load->view('pages/success');
            $this->load->view('templates/footer');
        }
    }
    public function hide($id)
    {
        $this->news_model->hide_news($id);

        $data['news'] = $this->prepareText($this->news_model->get_news());
		$data['images'] = $this->images_model->get_images();
        $data['title'] = 'News';
        $data['menu_item'] = 'news';

        $this->load->view('templates/header', $data);
        $this->load->view('news/index', $data);
        $this->load->view('templates/footer');
    }
    public function show($id)
    {
        $this->news_model->show_news($id);
        $data['news'] = $this->prepareText($this->news_model->get_news());
		$data['images'] = $this->images_model->get_images();
        $data['title'] = 'News';
        $data['menu_item'] = 'news';

        $this->load->view('templates/header', $data);
        $this->load->view('news/index', $data);
        $this->load->view('templates/footer');
    }
	public function delete($id)
	{
		$this->news_model->delete($id);
		echo "<br /><br /><br /><p>News removed. Uppdater för att se ändringen.</p>";
	}
	public function update($id){
		$newTitle 	= $this->input->post('title');
		$newText 	= $this->input->post('text');
		$this->news_model->update($id, $newTitle, $newText);
		echo "<br /><br /><br /><p>Your news have been updated.<br />Refresh browser to see chagnes. </p>";
	}

	/**
	 *
	 * Function to make URLs into links
	 *
	 * @param string The url string
	 *
	 * @return string
	 *
	 **/
//	function makeLink($string){
//
//		/*** make sure there is an http:// on all URLs ***/
//		$string = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2",$string);
//		/*** make all URLs links ***/
//		$string = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">$1</A>",$string);
//		/*** make all emails hot links ***/
//		$string = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<A HREF=\"mailto:$1\">$1</A>",$string);
//
//		return $string;
//	}
	function makeLink($post)
	{ // Disclaimer: This "URL plucking" regex is far from ideal.
		$post = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2",$post);
		$pattern = '!http://[a-z0-9\-._~\!$&\'()*+,;=:/?#[\]@%]+!i';
		$replace='_handle_URL_callback';
		return preg_replace_callback($pattern,$replace, $post);
	}
}
function _handle_URL_callback($matches)
{ // preg_replace_callback() is passed one parameter: $matches.
	if (preg_match('/\.(?:jpe?g|png|gif|JPG|JPEG)(?:$|[?#])/', $matches[0]))
	{ // This is an image if path ends in .GIF, .PNG, .JPG or .JPEG.
		return $matches[0];
	} // Otherwise handle as NOT an image.
	return '<a href="'. $matches[0] .'">'. $matches[0] .'</a>';
}
