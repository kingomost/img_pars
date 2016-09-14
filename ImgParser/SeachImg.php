<?php
namespace ImgParser;

use simple_html_dom;

class SeachImg {

    protected $keyword                  = false;
    private $pjs_proga                  = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'phantomjs.exe';
    private $pjs_script                 = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'rasterize.js';
    private $pjs_morguefile_script      = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'morguefile.js';
    private $pjs_content_script         = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'content.js';

    private $pjs_content_flickr         = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'content_flickr.js';
    private $pjs_script_flickr          = \B_DIR.\DIR_S_R.'LIB'.\DIR_S_R.'phantomJS'.\DIR_S_R.'rasterize_flickr.js';

    public function __construct ()
    {
        #code.........
    }

    public function pixabay_com ($max_count, $dir_for_write)
    {
        #запрос картинок у pixabay.com
        #$max_count - максимальное количество картинок, которое нужно взять
        $result_img_arr = [];
        for ($i=0; $i<25; $i++) {
            $html = $this->SetCurl($this->do_url_pixabay_com($i), 'https://pixabay.com/');
            $html_obj = new simple_html_dom();
            $html_obj->load($html);
            $list_img = $html_obj->find('div#photo_grid div');
            for ($j=0; $j<count($list_img); $j++) {
                $atribut = 'data-url';
                $buf_link = 'https://pixabay.com' . $list_img[$j]->find('a',0)->href;
                $buf_img_link_640 = 'https://pixabay.com' . $list_img[$j]->find('a',0)->find('img',0)->$atribut;
                $buf_text_tag = $list_img[$i]->find('a',0)->plaintext;
                $result_img_arr[] = [ $buf_link, $buf_img_link_640, $buf_text_tag ];
                $buf_file_name = array_pop(explode('/', $buf_img_link_640));
                $buf_dir_name = str_replace('.', '_', $buf_file_name);
                if (!is_dir($dir_for_write.\DIR_S_R.$buf_dir_name)) {
                    mkdir($dir_for_write.\DIR_S_R.$buf_dir_name);
                } elseif(is_dir($dir_for_write.\DIR_S_R.$buf_dir_name) && is_file($dir_for_write.\DIR_S_R.$buf_file_name)) {
                    continue;
                }
                file_put_contents($dir_for_write.\DIR_S_R.$buf_file_name, $this->SetCurl($buf_img_link_640, 'https://pixabay.com/'));
                exec ( escapeshellcmd ( $this->pjs_proga . ' ' . 
                                        $this->pjs_script . ' ' . 
                                        $buf_link . ' ' . 
                                        $dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'license.jpg' ) );
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'link_page.txt', $buf_link);
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'link_img.txt', $buf_img_link_640);
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'text_tag.txt', $buf_text_tag);
                if (count($result_img_arr)>=$max_count) break;
            }
            if (count($list_img)<99 || count($result_img_arr)>=$max_count) break;
            unset($html_obj);
        }
        return $result_img_arr;
    }

    protected function do_url_pixabay_com ($position = 0)
    {
        if ($position === 0) {
            $url = 'https://pixabay.com/en/photos/?q=' . 
                        urlencode($this->keyword) . 
                            '&image_type=photo&cat=&min_height=&min_width=&order=latest';
        } else {
            $url = 'https://pixabay.com/en/photos/?min_height=&image_type=photo&cat=&q=' . 
                        urlencode($this->keyword) . 
                            '&min_width=&order=latest&pagi=' . 
                                $position;
        }
        return $url;
    }

    public function morguefile_com ($max_count, $dir_for_write)
    {
        #запрос картинок у http://morguefile.com/
        #$max_count - максимальное количество картинок, которое нужно взять
        $result_img_arr = [];
        for ($i=1; $i<25; $i++) {
            $url = $this->do_url_morguefile_com($i);
            $file_data = exec ( escapeshellcmd (    $this->pjs_proga . ' ' . 
                                                    $this->pjs_content_script . ' ' . 
                                                    $url . ' ' . 
                                                    $dir_for_write.\DIR_S_R.'buf.jpg' ) );
            
            $html = json_decode($file_data);
           //print_r($html);
            //exit;

            $html_obj = new simple_html_dom();
            $html_obj->load($html);
            $list_img = $html_obj->find('div.scrolld-item');
            for ($j=0; $j<count($list_img); $j++) {
                $atribut_id = 'data-id';
                $atribut_jpg = 'data-jpg';
                $buf_link = 'http://morguefile.com/p/' . $list_img[$j]->$atribut_id;
                $buf_img_link_640 = $list_img[$j]->$atribut_jpg;
                $buf_text_tag = $list_img[$j]->find('img[alt]',0)->alt;

                //print_r($list_img[$j]->innertext);
                //print_r('<hr/>');
                //print_r($buf_link);
                //print_r('<hr/>');
                //print_r($buf_img_link_640);
                //print_r('<hr/>');
                //print_r($buf_text_tag);
                //print_r('<hr/><hr/><hr/>');

                $result_img_arr[] = [ $buf_link, $buf_img_link_640, $buf_text_tag ];
                $buf_file_name = array_pop(explode('/', $buf_img_link_640));
                $buf_dir_name = str_replace('.', '_', $buf_file_name);
                if (!is_dir($dir_for_write.\DIR_S_R.$buf_dir_name)) {
                    mkdir($dir_for_write.\DIR_S_R.$buf_dir_name);
                } elseif(is_dir($dir_for_write.\DIR_S_R.$buf_dir_name) && is_file($dir_for_write.\DIR_S_R.$buf_file_name)) {
                    continue;
                }
                file_put_contents($dir_for_write.\DIR_S_R.$buf_file_name, $this->SetCurl($buf_img_link_640, 'http://morguefile.com/'));
                exec ( escapeshellcmd ( $this->pjs_proga . ' ' . 
                                        $this->pjs_morguefile_script . ' ' . 
                                        $buf_link . ' ' . 
                                        $dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'license.jpg' ) );
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'link_page.txt', $buf_link);
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'link_img.txt', $buf_img_link_640);
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'text_tag.txt', $buf_text_tag);
                if (count($result_img_arr)>=$max_count) break;
                
            }
            if (count($list_img)<5 || count($result_img_arr)>=$max_count) break;
            unset($html_obj);
        }
        return $result_img_arr;
    }

    protected function do_url_morguefile_com ($position = 0)
    {
       $url = 'http://morguefile.com/search/morguefile/' . $position . '/' . urlencode($this->keyword) . '/pop';
        return $url;
    }

        public function flickr_com ($max_count, $dir_for_write, $license)
    {

        #запрос картинок у http://flickr.com/
        #$max_count - максимальное количество картинок, которое нужно взять

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

        $period_day = 14;
        $max_taken_date = (ceil(time()/(60*60*24)) + 0.75)*60*60*24 - 1 - 1200*60*60*24 ;
        $min_taken_date = $max_taken_date - $period_day*60*60*24 - 1200*60*60*24 ;
        $license = is_array($license) ? implode(',', $license) : $license ;

        $result_img_arr = [];

        for ($i=0; $i<100; $i++) {
            $url = $this->do_url_flickr_com($min_taken_date, $max_taken_date, $license);
            //$html = $this->SetCurl($url, 'https://flickr.com/');
            $file_data = exec ( escapeshellcmd (    $this->pjs_proga . ' ' . 
                                                    $this->pjs_content_flickr . ' ' . 
                                                    $url . ' ' . 
                                                    $dir_for_write.\DIR_S_R.'buf.jpg' ) );
            
            $html = json_decode($file_data);
            //print_r($html);
            //print_r('<hr/><hr/><hr/>');
            //print_r('<hr/><hr/><hr/>');
            //exit;
            $html_obj = new simple_html_dom();
            $html_obj->load($html);
            $list_img = $html_obj->find('div.awake');
            for ($j=0; $j<count($list_img); $j++) {

                //$buf = $list_img[$j]->find('div.interaction-view', 0)->outertext;

                //print_r($list_img[$j]->outertext);

                $buf = $list_img[$j]->find('div.tool a', 0);
                $buf_link = 'https://flickr.com' . $buf->href;

                $buf_dir_name = explode('/', $buf_link);
                $buf_dir_name = array_values(array_diff($buf_dir_name, ['', ' ']));
                $buf_dir_name = implode('_', [ $buf_dir_name[count($buf_dir_name)-2], $buf_dir_name[count($buf_dir_name)-1] ]);
                if (!is_dir($dir_for_write.\DIR_S_R.$buf_dir_name)) {
                    mkdir($dir_for_write.\DIR_S_R.$buf_dir_name);
                } elseif(is_dir($dir_for_write.\DIR_S_R.$buf_dir_name) && is_file($dir_for_write.\DIR_S_R.$buf_file_name)) {
                    continue;
                }
                $file_data = exec ( escapeshellcmd (    $this->pjs_proga . ' ' . 
                                                        $this->pjs_script_flickr . ' ' . 
                                                        $buf_link . ' ' . 
                                                        $dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'license.png' ) );
                $html = json_decode($file_data);
                $html_obj_buf = new simple_html_dom();
                $html_obj_buf->load($html);
                $base_img = 'https:' . $html_obj_buf->find('img.main-photo', 0)->src;
                $buf_text_tag = $html_obj_buf->find('img.main-photo', 0)->alt;
                $list_tag = $html_obj_buf->find('ul.tags-list li.tag');
                $list_tag_arr = [];
                for ($k=0; $k<count($list_tag); $k++) {
                    $list_tag_arr[] = trim($list_tag[$k]->plaintext);
                }
                $list_tag = implode(', ', $list_tag_arr);
                $autor_name = $html_obj_buf->find('a.owner-name', 0)->plaintext;
                //$autor_photo = $html_obj_buf->find('div.avatar', 0)->outertext;

                $buf_file_name = array_pop(explode('/', $base_img));
                $fyle_tipe = array_pop(explode('.', $buf_file_name));
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.'.'.$fyle_tipe, $this->SetCurl($base_img, 'https://flickr.com/'));

                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'link_page.txt', $buf_link);
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'link_img.txt', $base_img);
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'text_tag.txt', $buf_text_tag);

                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'list_tag.txt', $list_tag);
                file_put_contents($dir_for_write.\DIR_S_R.$buf_dir_name.\DIR_S_R.'autor_name.txt', $autor_name);

                print_r($buf_link); print_r('<br/>');
                print_r($base_img); print_r('<br/>');
                print_r($buf_text_tag); print_r('<br/>');
                print_r($list_tag); print_r('<br/>');
                print_r($autor_name); print_r('<br/>');
                print_r($autor_photo); print_r('<br/>');
                //print_r($html); print_r('<br/>');
                print_r('<hr/><hr/>');
                unset($html_obj_buf);
                $result_img_arr[] = [$buf_link, $base_img, $buf_text_tag, $list_tag, $autor_name, $autor_photo];

            }
            unset($html_obj);
            if (count($result_img_arr)>=$max_count) break;
            if (count($list_img) < 5 && $period_day<100 ) $period_day++;
            if (count($list_img) > 12 && $period_day>2)  $period_day--;
            $max_taken_date = $min_taken_date - 1;
            $min_taken_date -= $period_day*60*60*24;
        }

        return $result_img_arr;
    }

    protected function do_url_flickr_com ($min_taken_date, $max_taken_date, $license)
    {
       $url = 'https://www.flickr.com/search/?text=' . urlencode($this->keyword) . '&media=photos&license=' . urlencode($license) . '&min_taken_date=' . $min_taken_date . '&max_taken_date=' . $max_taken_date ;
        return $url;
    }


    public function set_keyword ($keyword)
    {
        $this->keyword = $keyword;
        return true;
    }



    protected function SetCurl ($url, $referer='http://google.com/', $data=null, $proxy=null, $options=null) 
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