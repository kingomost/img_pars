<?php
namespace ImgParser;
use simple_html_dom;

        #No Copyright + Yes commercial purposes + All what you want => Yes remix, Yes change
        #https://www.flickr.com/commons/usage/
        #$license = 7 No known copyright restrictions. Help us catalog the world’s public photo archives. (The copyright is in the public domain because it has expired; The copyright was injected into the public domain for other reasons, such as failure to adhere to required formalities or conditions; The institution owns the copyright but is not interested in exercising control; or The institution has legal rights sufficient to authorize others to use the work without restrictions.)

        #No Copyright + Yes commercial purposes + All what you want => Yes remix, Yes change
        #https://creativecommons.org/publicdomain/zero/1.0/
        #$license = 9 CC0 1.0 Universal (CC0 1.0) Public Domain Dedication (The person who associated a work with this deed has dedicated the work to the public domain by waiving all of his or her rights to the work worldwide under copyright law, including all related and neighboring rights, to the extent allowed by law. You can copy, modify, distribute and perform the work, even for commercial purposes, all without asking permission. See Other Information below.)

        #No Copyright + Yes commercial purposes + All what you want => Yes remix, Yes change
        #https://creativecommons.org/publicdomain/mark/1.0/
        #$license = 10 Public Domain Mark 1.0 (This work has been identified as being free of known restrictions under copyright law, including all related and neighboring rights. You can copy, modify, distribute and perform the work, even for commercial purposes, all without asking permission. See Other Information below.)

        #Yes Copyright + Yes commercial purposes + If remix => must say it
        #https://creativecommons.org/licenses/by/2.0/
        #$license = 4 Attribution 2.0 Generic (CC BY 2.0) (You are free to: Share — copy and redistribute the material in any medium or format. Adapt — remix, transform, and build upon the material for any purpose, even commercially. The licensor cannot revoke these freedoms as long as you follow the license terms. Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use. No additional restrictions — You may not apply legal terms or technological measures that legally restrict others from doing anything the license permits.)

        #Yes Copyright + Yes commercial purposes + If remix => must say it and remix license must CC BY-SA 2.0 too
        #https://creativecommons.org/licenses/by-sa/2.0/
        #$license = 5 Attribution-ShareAlike 2.0 Generic (CC BY-SA 2.0) (You are free to: Share — copy and redistribute the material in any medium or format. Adapt — remix, transform, and build upon the material for any purpose, even commercially. The licensor cannot revoke these freedoms as long as you follow the license terms. Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use. ShareAlike — If you remix, transform, or build upon the material, you must distribute your contributions under the same license as the original.)

        #Yes Copyright + Yes commercial purposes + No remix, no change
        #https://creativecommons.org/licenses/by-nd/2.0/
        #$license = 6 Attribution-NoDerivs 2.0 Generic (CC BY-ND 2.0) (Share — copy and redistribute the material in any medium or format for any purpose, even commercially. The licensor cannot revoke these freedoms as long as you follow the license terms. Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use. NoDerivatives — If you remix, transform, or build upon the material, you may not distribute the modified material. No additional restrictions — You may not apply legal terms or technological measures that legally restrict others from doing anything the license permits.)

        //НИЖЕ ПОГРАНИЧНЫЕ ЛИЦЕНЗИИ, ТК ПУБЛИКАЦИЯ ФОТО В СТАТЬЕ НЕ ЯВЛЯЕТСЯ КОММЕРЧЕСКИМ ИСПОЛЬЗОВАНИЕМ И ВПОЛНЕ ДОПУСТИМО ЛИЦЕНЗИЕЕЙ. НО НЕЛЬЗЯ ИССПОЛЬЗОВАТЬ НАПРИМЕР В РЕКЛАМЕ.

        #Yes Copyright + No commercial purposes + If remix => must say it
        #https://creativecommons.org/licenses/by-nc/2.0/
        #$license = 2 Attribution-NonCommercial 2.0 Generic (CC BY-NC 2.0) (You are free to: Share — copy and redistribute the material in any medium or format Adapt — remix, transform, and build upon the material. The licensor cannot revoke these freedoms as long as you follow the license terms. Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use. NonCommercial — You may not use the material for commercial purposes. No additional restrictions — You may not apply legal terms or technological measures that legally restrict others from doing anything the license permits.)

        #Yes Copyright + No commercial purposes + No remix, no change
        #https://creativecommons.org/licenses/by-nc-nd/2.0/
        #$license = 3 Attribution-NonCommercial-NoDerivs 2.0 Generic (CC BY-NC-ND 2.0) (You are free to: Share — copy and redistribute the material in any medium or format. The licensor cannot revoke these freedoms as long as you follow the license terms. Under the following terms: Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use. NonCommercial — You may not use the material for commercial purposes. NoDerivatives — If you remix, transform, or build upon the material, you may not distribute the modified material.)

        //"АМЕРИКАНСКАЯ" ЛИЦЕНЗИЯ - КОТОРУЮ ДАЮТ ГОС ОРГАНИЗАЦИИ ДЛЯ СВОБОДНОГО РАСПРОСТРАНЕНИЯ МАТЕРИАЛА
        //ЧАСТО В ОПИСАНИИ ЛИЦЕНЗИЯ РАСШИРЯЕТСЯ МАКСИМАЛЬНО [State Department photo/ Public Domain] - НЕ ВСЕГДА

        #??? МУТНО ???
        #https://www.usa.gov/government-works
        #$license = 8 U.S. Government Works (United States government creative works, including writing, images, and computer code, are usually prepared by officers or employees of the United States government as part of their official duties. A government work is generally not subject to copyright in the United States and there is generally no copyright restriction on reproduction, derivative works, distribution, performance, or display of a government work. Most U.S. government creative works such as writing or images are copyright-free. But before you use a U.S. government work, check to make sure it does not fall under one of these exceptions.)

        #$license = [7,9,10] No known copyright restrictions
        #$license = [4,5,9,10] Commercial use & mods allowed
        #$license = [2,3,4,5,6,9] All creative commons

class Flickr
{
    public $pjs = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'phantomjs.exe';
    public $pjs_render_js_search = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'rasterize_flickr_0.js';
    public $pjs_render_js_page = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'rasterize_flickr_1.js';
    public $screen_file = 'screen_page.jpg';
    public $scrap_file = 'scrap_file.';
    public $list_all_pages   = [];
    public $list_scrap_pages = [];
    public $keyword;
    public $dir_for_write;
    public $max_count;
    public $license;
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
    
    public function __construct($keyword, $dir_for_write, $max_count, $license) 
    {
        $this->keyword = $keyword;
        $this->dir_for_write = $dir_for_write;
        $this->max_count = $max_count;
        $this->license = $license;
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
        $url_img = 'https:' . $html_obj->find('img.main-photo', 0)->src;
        return [
            'site' => 'https://www.flickr.com',
            'url_page' => $url,
            'url_img' => $url_img,
        ];
    }

    public function p_author($html, $html_obj, $url, $dir_for_save)
    {
        $nick_name = trim($html_obj->find('div.sub-photo-container div.sub-photo-left-view div.photo-attribution', 0)->find('div.attribution-info', 0)->find('a[href]', 0)->plaintext);
        $page = $html_obj->find('div.sub-photo-container div.sub-photo-left-view div.photo-attribution', 0)->find('div.attribution-info', 0)->find('a[href]', 0)->href;
        //$page = explode('/', $page); $page = array_diff($page, ['', ' ']); $page = array_values($page); $page = array_pop($page); 
        $page = str_replace('photos/', 'people/', $page);
        $page = 'https://www.flickr.com' . $page;
        $html_page = $this->return_html_search_page($page);
        $autor_page_obj = new simple_html_dom();
        $autor_page_obj->load($html_page);
        $name = $autor_page_obj->find('div.profile-section dl', 0)->find('dd', 0)->plaintext;
        $about = [];
        $buf = $autor_page_obj->find('div.profile-section dl');
        foreach ($buf as $element) $about[] = trim($element->plaintext);
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
        $all_tags = [];

        $buf = $html_obj->find('div.sub-photo-tags-tag-view ul', 0);
        if (is_object($buf)) foreach ($buf->find('li.tag') as $tag) $base_tags[] = trim($tag->plaintext);

        $buf = $html_obj->find('div.sub-photo-tags-tag-view ul', 0);
        if (is_object($buf)) foreach ($buf->find('li') as $tag) $all_tags[] = trim($tag->plaintext);

        $title = trim($html_obj->find('h1.photo-title', 0)->plaintext);
        $description = trim($html_obj->find('h2.photo-desc', 0)->plaintext);
        $comments = [];
        $buf = $html_obj->find('div.comments-holder ul.comments li.comment');
        foreach ($buf as $comment) {
            $comments[] = [
                'autor' => trim($comment->find('p.comment-author a[href]', 0)->plaintext),
                'autor_link' => 'https://www.flickr.com' . $comment->find('p.comment-author a[href]', 0)->href,
                'text' => trim($comment->find('div.comment-content', 0)->plaintext),
            ];
        }
        $created = trim($html_obj->find('div.date-taken span.date-taken-label', 0)->plaintext);
        $uploaded = trim($html_obj->find('div.date-taken span[title]', 0)->title);
        return [
            'base_tags' => $base_tags,
            'all_tags' => $all_tags,
            'title' => $title,
            'description' => $description,
            'comments' => $comments,
            'category' => '',
            'created' => $created,
            'uploaded' => $uploaded,
        ];
    }

    public function p_license($html, $html_obj, $url, $dir_for_save)
    {
        $license_data = [];

        switch ($this->license) {
            case 2:
                $license_data = [
                    'short_lable' => 'CC BY-NC 2.0',
                    'full_title' => 'Attribution-NonCommercial 2.0 Generic',
                    'link_to_license' => 'https://creativecommons.org/licenses/by-nc/2.0/',
                ];
                break;
            case 3:
                $license_data = [
                    'short_lable' => 'CC BY-NC-ND 2.0',
                    'full_title' => 'Attribution-NonCommercial-NoDerivs 2.0 Generic',
                    'link_to_license' => 'https://creativecommons.org/licenses/by-nc-nd/2.0/',
                ];
                break;
            case 4:
                $license_data = [
                    'short_lable' => 'CC BY 2.0',
                    'full_title' => 'Attribution 2.0 Generic',
                    'link_to_license' => 'https://creativecommons.org/licenses/by/2.0/',
                ];
                break;
            case 5:
                $license_data = [
                    'short_lable' => 'CC BY-SA 2.0',
                    'full_title' => 'Attribution-ShareAlike 2.0 Generic',
                    'link_to_license' => 'https://creativecommons.org/licenses/by-sa/2.0/',
                ];
                break;
            case 6:
                $license_data = [
                    'short_lable' => 'CC BY-ND 2.0',
                    'full_title' => 'Attribution-NoDerivs 2.0 Generic',
                    'link_to_license' => 'https://creativecommons.org/licenses/by-nd/2.0/',
                ];
                break;
            case 7:
                $license_data = [
                    'short_lable' => 'No known copyright restrictions',
                    'full_title' => 'No known copyright restrictions',
                    'link_to_license' => 'https://www.flickr.com/commons/usage/',
                ];
                break;
            case 8:
                $license_data = [
                    'short_lable' => 'U.S. Government Works',
                    'full_title' => 'U.S. Government Works',
                    'link_to_license' => 'https://www.usa.gov/government-works',
                ];
                break;
            case 9:
                $license_data = [
                    'short_lable' => 'CC0 1.0',
                    'full_title' => 'CC0 1.0 Universal',
                    'link_to_license' => 'https://creativecommons.org/publicdomain/zero/1.0/',
                ];
                break;
            case 10:
                $license_data = [
                    'short_lable' => 'Public Domain Mark 1.0',
                    'full_title' => 'Public Domain Mark 1.0',
                    'link_to_license' => 'https://creativecommons.org/publicdomain/mark/1.0/',
                ];
                break;
            default:
                $license_data = [
                    'short_lable' => '',
                    'full_title' => '',
                    'link_to_license' => '',
                ];
        }
        
        $short_lable        = 'CC0 1.0';
        $full_title         = 'CC0 1.0 Universal (CC0 1.0) Public Domain Dedication';
        $link_to_license    = 'https://creativecommons.org/publicdomain/zero/1.0/deed.en';
        return $license_data;
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

    public function list_all_pages()
    {
        $all_list = [];
        $period_day = 55;
        $max_taken_date = (ceil(time()/(60*60*24)) + 0.75)*60*60*24 - 1;
        $min_taken_date = $max_taken_date - $period_day*60*60*24;
        $license = is_array($this->license) ? implode(',', $this->license) : $this->license ;
        for ($i=0; $i<12; $i++) {
            $url            = $this->url_paginate($min_taken_date, $max_taken_date, $license);
            $html           = $this->return_html_search_page($url);
            $link_pages     = $this->seach_link_pages($html);
            $all_list       = array_values(array_unique(array_merge($all_list, $link_pages)));
            if (count($all_list) > $this->max_count) break;
            if (count($link_pages) < 10 && $period_day<150 ) $period_day += 15;
            if (count($link_pages) > 15 && $period_day>20)  $period_day -= 15;
            //echo $i." +".count($link_pages)." count = ".count($all_list)." period: ".$period_day." ".date("d.m.Y", $max_taken_date)." - ".date("d.m.Y", $min_taken_date)."<hr>";
            $max_taken_date = $min_taken_date - 1;
            $min_taken_date -= $period_day*60*60*24;
        }
        return $all_list;
    }

    public function return_html_search_page($url)
    {
        $file       = 'not_save';
        $file_data  = exec(escapeshellcmd($this->pjs . ' ' . $this->pjs_render_js_search . ' ' . $url . ' ' . $file));
        $html       = json_decode($file_data);
        return $html;
    }

    public function seach_link_pages($html)
    {
        $link_pages = [];
        $html_obj = new simple_html_dom();
        $list_img = $html_obj->load($html)->find('div.awake');
        foreach ($list_img as $element) {
            $buf = $element->find('div.tool a[href]', 0)->href;
            if (is_string($buf)) $link_pages[] = 'https://flickr.com' . $buf;
        }
        unset($html_obj);
        return $link_pages;
    }

    public function url_paginate($min_taken_date, $max_taken_date, $license)
    {
        $url = 'https://www.flickr.com/search/?text=' . urlencode($this->keyword) . '&media=photos&license=' . urlencode($license) . '&min_taken_date=' . $min_taken_date . '&max_taken_date=' . $max_taken_date ;
        return $url;
    }

    public function SetCurl ($url, $referer='https://flickr.com/', $data=null, $proxy=null, $options=null) 
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