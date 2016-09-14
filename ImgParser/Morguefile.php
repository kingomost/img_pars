<?php
namespace ImgParser;
use simple_html_dom;

class Morguefile
{
    public $pjs = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'phantomjs.exe';
    public $pjs_render_js_search = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'rasterize_morguefile_0.js';
    public $pjs_render_js_page = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'rasterize_morguefile_1.js';
    public $screen_file = 'screen_page.jpg';
    public $scrap_file = 'scrap_file.';
    public $list_all_pages   = [];
    public $list_scrap_pages = [];
    public $keyword;
    public $dir_for_write;
    public $max_count;
    public $list_properties = [

        'source' => [
            'site' => '',
            'url_page' => '',
            'url_img' => '',
        ],

        'author' => [
            'name' => '',
            'nick_name' => '',
            'page' => '',
            'about' => '',
            'contacts' => '',
        ],

        'about' => [
            'base_tags' => '',
            'all_tags' => '',
            'title' => '',
            'description' => '',
            'comments' => '',
            'category' => '',
            'created' => '',
            'uploaded' => '',
        ],

        'license' => [
            'short_lable' => '',
            'full_title' => '',
            'link_to_license' => '',
        ],

        'recreate' => [
            'time_load' => '',
            'file' => '',
            'type' => '',
            'size' => '',
            'width' => '',
            'height' => '',
            'screenshot_page' => '',
        ],

        'more' => [
        ],

    ];
    
    public function __construct($keyword, $dir_for_write, $max_count, $license='') 
    {
        $this->keyword = $keyword;
        $this->dir_for_write = $dir_for_write;
        $this->max_count = $max_count;
    }

    public function do_it()
    {
        $this->list_all_pages = $this->list_all_pages();
        for ($i=0, $i_max=count($this->list_all_pages); $i<$i_max; $i++) {
            //??? делать проверку на наличие этой картинки уже бд
            //DO scrap_page
            $buf = $this->scrap_page($this->list_all_pages[$i]);
            if ($buf) $this->list_scrap_pages[] = $this->list_all_pages[$i];
            if (count($this->list_scrap_pages) >= $this->max_count) break;
        }
        return count($this->list_scrap_pages);
    } 

    public function scrap_page($url, $dir = false)
    {
        if ($dir) {
            $dir_for_save = $dir;
        } else {
            while ($dir_for_save = $this->dir_for_write . DIRECTORY_SEPARATOR . time()) {
                if (!is_dir($dir_for_save)) break;
            }
        }
        @mkdir($dir_for_save);
        $html = $this->do_screen_return_html($url, $dir_for_save);
        //DO scrap_properties
        $list_properties = $this->scrap_properties($html, $url, $dir_for_save);
        //DO check 
        if (
            is_file($dir_for_save . DIRECTORY_SEPARATOR . $list_properties['recreate']['file']) &&
            filesize($dir_for_save . DIRECTORY_SEPARATOR . $list_properties['recreate']['file']) > 1024*4 &&
            is_file($dir_for_save . DIRECTORY_SEPARATOR . $list_properties['recreate']['screenshot_page']) &&
            filesize($dir_for_save . DIRECTORY_SEPARATOR . $list_properties['recreate']['screenshot_page']) > 1024*56
        ) {
            file_put_contents ($dir_for_save . DIRECTORY_SEPARATOR . 'data.txt', json_encode($list_properties));
            return true;
        }
        return false;
    }

    public function scrap_properties($html, $url, $dir_for_save)
    {
        $list_pr_es = $this->list_properties;
        $html_obj = new simple_html_dom();
        $html_obj->load($html);
        foreach ($list_pr_es as $base => &$value) {
            try {
                if (method_exists($this, 'p_'.$base)) $value = $this->{'p_'.$base}($html, $html_obj, $url, $dir_for_save);
            } catch (Exception $e) {
                $this->class_log($e);
            }
        }
        unset($value);
        unset($html_obj);
        return $list_pr_es;
    }

    public function p_source($html, $html_obj, $url, $dir_for_save)
    {
        $url_img = $html_obj->find('div#img-view-dom', 0)->find('img[src]', 0)->src;
        return [
            'site' => 'http://morguefile.com/',
            'url_page' => $url,
            'url_img' => $url_img,
        ];
    }

    public function p_author($html, $html_obj, $url, $dir_for_save)
    {
        $nick_name = $html_obj->find('div.more-info-view div div div.moreInfo', 0)->find('span', 0)->find('a[onclick]', 0)->plaintext;
        $page = $html_obj->find('div.more-info-view div div div.moreInfo', 0)->find('span', 0)->find('a[onclick]', 0)->onclick;
        $page = explode(',', $page); $page = trim(array_pop($page));
        preg_match_all("~\'(.+)\'\);~", $page, $buf);
        $page = 'http://morguefile.com/' . $buf[1][0];
        $autor_page_obj = new simple_html_dom();
        $autor_page_obj->load($this->SetCurl($page));
        $about = [];
        $about[] = $autor_page_obj->find('div.scrllInterface div ul.profile-data', 0)->plaintext;
        $about[] = $autor_page_obj->find('div.scrllInterface div div.bio', 0)->content;
        unset($autor_page_obj);
        return [
            'name' => '',
            'nick_name' => $nick_name,
            'page' => $page,
            'about' => $about,
            'contacts' => '',
        ];
    }

    public function p_about($html, $html_obj, $url, $dir_for_save)
    {
        $base_tags = [];
        $buf = $html_obj->find('div.keys ul.keywords', 0)->find('li');
        for ($i=1; $i<count($buf); $i++) $base_tags[] = $buf[$i]->plaintext;
        $uploaded = $html_obj->find('div.more-info-view div div div.moreInfo', 0)->find('span a[href]', 0)->plaintext;
        return [
            'base_tags' => $base_tags,
            'all_tags' => '',
            'title' => '',
            'description' => '',
            'comments' => '',
            'category' => '',
            'created' => '',
            'uploaded' => $uploaded,
        ];
    }

    public function p_license($html, $html_obj, $url, $dir_for_save)
    {
        $short_lable        = 'Morguefile license';
        $full_title         = $html_obj->find('div.more-info-view div div div.moreInfo', 0)->find('span', 2)->find('a[href]', 0)->plaintext;
        $link_to_license    = 'http://morguefile.com/license';
        return [
            'short_lable' => $short_lable,
            'full_title' => $full_title,
            'link_to_license' => $link_to_license,
        ];
    }

    public function p_recreate($html, $html_obj, $url, $dir_for_save)
    {
        
        $time_load = time();
        $buf = self::p_source($html, $html_obj, $url, $dir_for_save);
        $type = explode('/', $buf['url_img']); $type = array_pop($type); $type = explode('.', $type); $type = array_pop($type);
        $file = $this->scrap_file . $type;
        file_put_contents($dir_for_save . DIRECTORY_SEPARATOR . $file, $this->SetCurl($buf['url_img']));
        $size = filesize($dir_for_save . DIRECTORY_SEPARATOR . $file);
        $buf = getimagesize($dir_for_save . DIRECTORY_SEPARATOR . $file);
        $width = $buf[0];
        $height = $buf[1];
        $screenshot_page = $this->screen_file;
        return [
            'time_load' => $time_load,
            'file' => $file,
            'type' => $type,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'screenshot_page' => $screenshot_page,
        ];
    }

    public function class_log($e)
    {
        $class_name = str_replace('\\', '_', get_class($this));
        file_put_contents('log_'.$class_name.'.txt', time()."\r\n".$e->getMessage()."\r\n\r\n", FILE_APPEND);
    }

    public function do_screen_return_html($url, $dir_for_save)
    {
        $file       = $dir_for_save . DIRECTORY_SEPARATOR . $this->screen_file;
        $file_data  = exec(escapeshellcmd($this->pjs . ' ' . $this->pjs_render_js_page . ' ' . $url . ' ' . $file));
        $html       = json_decode($file_data);
        return $html;
    }

    public function return_html_search_page($url)
    {
        $file       = 'not_save';
        $file_data  = exec(escapeshellcmd($this->pjs . ' ' . $this->pjs_render_js_search . ' ' . $url . ' ' . $file));
        $html       = json_decode($file_data);
        return $html;
    }

    public function list_all_pages()
    {
        $all_list = [];
        for ($i=0; $i<100; $i++) {
            $url            = $this->url_paginate($i);
            $html           = $this->return_html_search_page($url);
            $link_pages     = $this->seach_link_pages($html);
            $all_list       = array_values(array_unique(array_merge($all_list, $link_pages)));
            if (count($link_pages)<7 || count($all_list) > $this->max_count) break;
        }
        return $all_list;
    }

    public function seach_link_pages($html)
    {
        $link_pages = [];
        $html_obj = new simple_html_dom();
        $list_img = $html_obj->load($html)->find('div.scrolld-item');
        foreach ($list_img as $element) {
            $buf = $element->{'data-id'};
            if (is_string($buf)) $link_pages[] = 'http://morguefile.com/p/' . $buf;
        }
        unset($html_obj);
        return $link_pages;
    }

    public function url_paginate($position)
    {
        $url = 'http://morguefile.com/search/morguefile/' . $position . '/' . urlencode($this->keyword) . '/pop';
        return $url;
    }

    public function SetCurl ($url, $referer='http://morguefile.com/', $data=null, $proxy=null, $options=null) 
    {
        $process = curl_init ($url);
        if(!is_null($data)) {
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, $data);
        }
        if(!is_null($options)) {
            curl_setopt_array($process,$options);
        }
        if(!is_null($proxy)) {
            curl_setopt($process, CURLOPT_PROXY, $proxy);
        }
        if(mb_substr_count($url,'https://','utf-8')>0) {
            curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_COOKIEFILE, 'cookies.txt');
        curl_setopt($process, CURLOPT_COOKIEJAR, 'cookies.txt');
        curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36');
        //curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; FreeBSD i386; en-EN; rv:1.9.1.10) Gecko/20100625 Firefox/3.5.10');
        curl_setopt ($process , CURLOPT_REFERER , $referer);
        curl_setopt($process, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($process, CURLOPT_TIMEOUT, 5);
        @curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $resalt = curl_exec($process);
        curl_close($process);
        return $resalt;
    }
}