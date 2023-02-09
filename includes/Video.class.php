<?php

/**
 * ====================================================================================
 *                           Google Drive Proxy Player (c) CodySeller
 * ----------------------------------------------------------------------------------
 * @copyright This software is exclusively sold at codyseller.com. If you have downloaded this
 *  from another site or received it from someone else than me, then you are engaged
 *  in an illegal activity. You must delete this software immediately or buy a proper
 *  license from https://codyseller.com/license.
 *
 *  Thank you for your cooperation and don't hesitate to contact me if anything :)
 * ====================================================================================
 *
 * @author CodySeller (http://codyseller.com)
 * @link http://codyseller.com
 * @license http://codyseller.com/license
 */


class video {

    protected $title;
    protected $servers;
    protected $type = '';
    protected $tmpLinks = [];

    protected $link = [];
    protected $file = [];
    protected $error = '';
    protected $isHls = false;

    protected $sources = [];
    protected $subs = [];
    protected $poster;
    protected $player;

    protected $isHit = false;

    public function __construct($link) {
        $this->link = $link;
        $this->file = $link->getObj();
        $this->player = FH::getConfig('player') == 'jw' ? 'jw' : 'plyr';
        $this->servers = new Server;
    }

    public function load() {

        $this->setMainServer();
        $this->setLoadervers();
        $this->setHlsLinks();
        $this->setAltServers();

        $this->loadSources();

        return !empty($this->sources) ? true : false;

    }

    public function getData() {

        $videoData = ['title' => $this->getTitle() , 'sources' => $this->getSources() , 'subs' => $this->getSubs() , 'poster' => $this->getPoster() , 'type' => $this->getType() , 'player' => $this->getPlayer() ];

        return $this->formatData($videoData);

    }

    protected function setLoadervers() {
        if (!FH::getConfig('streamRand')) {
            $tmpStreamServers = $this
                ->servers
                ->get('stream', 'active', 0);
            if (!empty($tmpStreamServers)) {
                foreach ($tmpStreamServers as $server) {
                    $this->tmpLinks[] = ['id' => $server['id'], 'type' => 'load', 'name' => $server['name']];
                }
            }
        }

    }

    protected function setHlsLinks() {

        $hlsLink = new HlsLink;
        $hlsLinks = $hlsLink->get($this
            ->link
            ->getId());
        $tmpHlsServers = $this
            ->servers
            ->get('hls', 'active', 0);
        $hlsServers = [];

        if (!empty($hlsLinks) && !empty($tmpHlsServers)) {
            foreach ($tmpHlsServers as $hlK => $hlV) {
                $hlsServers[$hlV['id']] = $hlV;
            }
            foreach ($hlsLinks as $link) {
                if ($link['status'] == 0) {
                    if (array_key_exists($link['server_id'], $hlsServers)) {
                        $this->tmpLinks[] = ['id' => $link['id'], 'type' => 'hls', 'name' => $hlsServers[$link['server_id']]['name']];
                    }

                }

            }
        }

    }

    protected function setAltServers() {

        $altLinks = $this->file['altLinks'];
        $altR = Helper::getAltR();

        if (!empty($altLinks) && is_array($altLinks)) {
            foreach ($altLinks as $l) {
                if ($l['status'] == 0) {

                    $rn = $l['type'];
                    if (array_key_exists(strtolower($l['type']) , $altR)) {
                        if (!empty($altR[strtolower($l['type']) ]['n'])) {
                            $rn = $altR[strtolower($l['type']) ]['n'];
                        }
                    }

                    $this->tmpLinks[] = ['id' => $l['id'], 'type' => 'alt', 'name' => $rn];
                }

            }
        }

    }

    protected function setMainServer() {
        if ($this->file['status'] == 0 && STREAM_WITH_MAIN_SERVER) {
            $this->tmpLinks[] = ['id' => $this->file['id'], 'type' => '', 'name' => 'Main server', ];
        }
    }

    protected function loadBalance($sources) {
        $sId = Helper::getReqData('sid');
        $acA = 'main_d_001';
        $server = $this
            ->servers
            ->getOne($sId);

        if (!empty($server)) {
            if (empty($sId)) {
                if (STREAM_WITH_MAIN_SERVER) {
                    $acT = ['main_d_001', $server['domain']];
                }
                else {
                    $acT = [$server['domain']];
                }

                $acA = $acT[array_rand($acT) ];
            }
            else {

                $acA = $server['domain'];
            }

            if ($acA != 'main_d_001') {

                $sources = Helper::addSD($sources, $acA);
                $this
                    ->servers
                    ->load($server);
                $this
                    ->servers
                    ->addPlayback();
            }
        }
        else {
            if (!STREAM_WITH_MAIN_SERVER) {
                $sources = [];
            }
            //reidrect to tmp file
            if (!empty($this->tmpLinks) && empty(Helper::getReqData('sid'))) {
                if (isset($this->tmpLinks[0])) {
                    $tmp = $this->tmpLinks[0];
                    $reidrectURI = $_SERVER['REQUEST_URI'] . '?sid=' . $tmp['id'] . '&t=' . $tmp['type'];
                    $reidrectURI = Helper::getDomain() . '' . $reidrectURI;
                    Helper::redirect($reidrectURI, true);
                }
            }

        }

        return $sources;

    }

    protected function loadSources() {

        $activeLinkData = $this->getActiveLink();
        $isAltOk = false;
        $sources = [];

        $mainLink = $activeLinkData['mainLink'];
        $updatedAt = $activeLinkData['updatedAt'];
        $type = $activeLinkData['type'];
        $isAlt = $activeLinkData['isAlt'];

        $this->type = $type;

        switch ($type) {
            case 'GDrive':

                if (empty($this->file['data']) || $this->file['status'] == 2) {
                    $this
                        ->link
                        ->refresh();
                    if (!$this
                        ->link
                        ->hasError()) {
                        if (!empty($this
                            ->link
                            ->obj['data'])) {
                            $this->file = $this
                                ->link
                                ->getObj();
                        }
                    }
                }

                if (!empty($this->file) && $this->file['status'] == 0) {

                    $sources = Helper::getDriveData($this->file);

                    $sid = Helper::getReqData('sid');
                    $t = Helper::getReqData('t');

                    if ((!empty($sid) && $t == 'load') || !STREAM_WITH_MAIN_SERVER || FH::getConfig('streamRand')) {
                        $sources = $this->loadBalance($sources);
                    }

                }

            break;
            case 'GPhoto':

                $gphoto = new GPhoto();
                $sources = $gphoto->get($mainLink);

            break;
            case 'OneDrive':

                $oneDrive = new OneDrive();
                $sources = $oneDrive->get($mainLink);

            break;
            case 'OkRu':

                $okru = new OkRu;
                $cache = new Cache('', 'okru');
                $fId = $okru->set($mainLink)->getId();

                $vData = $cache->get($fId);

                if (empty($vData) || $okru->isExpired($updatedAt)) {

                    $vData = $okru->get();

                    if (!empty($vData)) {
                        $this
                            ->link
                            ->save();
                        $cache->cr()
                            ->setKey($fId);
                        $cache->save($vData);
                    }

                }

                if (!empty($vData)) {
                    $sources = Helper::getOkRuData($vData);
                }

            break;
            case 'Yandex';

            $sources = [['file' => Yandex::getStreamURI($mainLink) , 'q' => '']];
            $this->isHls = true;

        break;
        case 'Direct':
            $pInfo = pathinfo($mainLink, PATHINFO_EXTENSION);

            if (!empty($pInfo) && $pInfo == 'm3u8') {
                $this->isHls = true;
            }

            $sources = [['file' => $mainLink, 'q' => '360']];

        break;
    }

    if ($isAlt) {
        $altId = Helper::getReqData('sid');
        $this
            ->link
            ->switch('alt');
        if ($this
            ->link
            ->load($altId)) {
            $isAltOk = true;
        }
    }

    if (empty($sources)) {

        $isBroken = true;

        if ($type != 'GDrive' && $isAltOk && STREAM_WITH_MAIN_SERVER) {
            if (!$this
                ->link
                ->isBroken()) {
                $this
                    ->link
                    ->broken();
            }
        }

        $sources = [['file' => FH::getConfig('default_video') , 'q' => '360']];

    }
    else {
        if ($type != 'GDrive' && $isAltOk) {
            if ($this
                ->link
                ->isBroken()) {
                $this
                    ->link
                    ->broken(false);
            }
        }

        $this->isHit = true;
    }

    $this->sources = $sources;

}

protected function getActiveLink() {

    $sid = Helper::getReqData('sid');
    $st = Helper::getReqData('t');

    $data = [];

    //main_link
    $data = ['mainLink' => $this->file['main_link'], 'type' => $this->file['type'], 'isAlt' => false, 'updatedAt' => $this->file['updated_at']];

    if (!empty($sid) && !empty($st)) {

        if ($st == 'hls') {

            //hls_link
            $hlsLink = new HlsLink;
            $hlsResp = $hlsLink->findById($sid);

            if (!empty($hlsResp)) {

                //get server
                $hlsServer = $this
                    ->servers
                    ->findById($hlsResp['server_id']);

                if (!empty($hlsServer) && $hlsServer['status'] == 0) {

                    //get hls link
                    $mainLink = MyHLS::getHlsLink($hlsServer['domain'], $hlsResp['file_id']);
                    $type = 'Direct';
                    $this->isHls = true;

                    //save
                    $this
                        ->servers
                        ->load($hlsServer);
                    $this
                        ->servers
                        ->addPlayback();

                }
                else {

                    //server not active
                    
                }

            }
            else {

                //hls link does not exist
                
            }

        }
        else if ($st == 'alt') {

            //alt_link
            $altIsExist = false;
            if (!empty($this->file['altLinks'])) {
                foreach ($this->file['altLinks'] as $alt) {
                    if ($alt['id'] == $sid) {
                        $mainLink = $alt['link'];
                        $type = $alt['type'];
                        $updatedAt = $alt['updated_at'];
                        $isAlt = true;
                        $altIsExist = true;
                    }
                }
            }

            if (!$altIsExist) {
                //alt link does not exist
                
            }

        }

    }

    if (isset($mainLink)) {
        $data['mainLink'] = $mainLink;
        $data['type'] = $type;
        if (isset($isAlt)) {
            $data['updatedAt'] = $updatedAt;
            $data['isAlt'] = $isAlt;
        }
    }

    // dnd($data);
    return $data;

}

public function getPlayer() {
    return $this->player;
}

public function getSubs() {
    $subs = $this->file['subtitles'];
    if (!empty($subs) && $this->isHit) {
        $subs = json_decode('[' . $subs . ']', true);

        if ($subs !== null) {
            return $subs;
        }
    }
    return '';
}

protected function formatData($data) {

    $sources = $subs = [];
    $isSubAuto = FH::getConfig('isAutoEnableSub');

    if ($this->isHls && HLS_WITH_PLYR) {
        $this->player = 'plyr';
    }

    switch ($this->getPlayer()) {
        case 'jw':

            //sources
            foreach ($data['sources'] as $s) {

                $sources[] = "{'label':'" . $s['q'] . "p','type':'video\/mp4','file':'" . $s['file'] . "'}";
            }

            // subtitles
            if (!empty($data['subs'])) {

                foreach ($data['subs'] as $subK => $sub) {

                    $default = $isSubAuto && $subK == 0 ? '"default": true,' : '';

                    $subs[] = '{' . $default . '  "kind": "captions","file": "' . Helper::getSubD($sub['file']) . '",  "label": "' . $sub['label'] . '"  }';

                }
                $data['subs'] = '[' . implode(',', $subs) . ']';
            }
            else {
                $data['subs'] = '[]';
            }

        break;
        case 'plyr':

            //sources
            if (!$this->isHls) {
                foreach ($data['sources'] as $s) {
                    $sources[] = "{ src: '" . $s['file'] . "',type: 'video/mp4', size: " . $s['q'] . " }";
                }
            }
            else {
                $data['sources'] = $data['sources'][0]['file'];
            }

            // subtitles
            if (!empty($data['subs'] && false)) {
                if (!$this->isHls) {
                    foreach ($data['subs'] as $k => $sub) {
                        $d = $k == 0 ? true : false;
                        $subs[] = "{'kind' : 'captions','label' : '" . $sub['label'] . "', 'src' : '" . Helper::getSubD($sub['file']) . "','default' : '" . $d . "'}";

                    }
                    $data['subs'] = '[' . implode(',', $subs) . ']';
                }
                else {
                    foreach ($data['subs'] as $k => $sub) {
                        $d = $k == 0 ? 'default' : '';

                        $subs[] = ' <track
                                            kind="captions"
                                            label="' . $sub['label'] . '"
                                            srclang="en"
                                            src="' . Helper::getSubD($sub['file']) . '"
                                            ' . $d . '
                                        />';

                    }
                    $data['subs'] = implode(' ', $subs);
                }

            }
            else {
                $data['subs'] = '[]';
            }

        break;

    }

    $data['isHls'] = $this->isHls;
    if (!($this->isHls && $this->getPlayer() == 'plyr')) $data['sources'] = '[' . implode(',', $sources) . ']';

    return $data;

}

public function getType() {
    return $this->type;
}

public function getSources() {
    return $this->sources;
}

public function getTmpLinks() {
    return $this->tmpLinks;
}

public function getTitle() {
    return $this->file['title'];
}

public function getPoster() {
    if (!empty($this->file['preview_img'])) {
        return Helper::getBanner($this->file['preview_img']);
    }
    else {
        if (!empty(FH::getConfig('default_banner'))) {
            return FH::getConfig('default_banner');
        }
    }
    return 'sdsa';

}

public function __destruct() {
    $this
        ->link
        ->viewed();
}

}

