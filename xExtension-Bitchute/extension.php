<?php

/**
 * Class BitchuteExtension
 *
 * Based on https://github.com/tunbridgep/freshrss-invidious
 *
 * @author Paul Tunbridge
 */
class BitchuteExtension extends Minz_Extension
{
    /**
     * Video player width
     * @var int
     */
    protected $width = 560;
    /**
     * Video player height
     * @var int
     */
    protected $height = 315;
    /**
     * Bitchute URL
     * @var string
     */
    protected $bitchute_url = 'bitchute.com';
    
    /**
     * Initialize this extension
     */
    public function init()
    {
        $this->registerHook('entry_before_display', array($this, 'embedBitchuteVideo'));
        $this->registerTranslates();
    }

    /**
     * Initializes the extension configuration, if the user context is available.
     * Do not call that in your extensions init() method, it can't be used there.
     */
    public function loadConfigValues()
    {
        if (!class_exists('FreshRSS_Context', false) || null === FreshRSS_Context::$user_conf) {
            return;
        }

        if (FreshRSS_Context::$user_conf->bitchute_player_width != '') {
            $this->width = FreshRSS_Context::$user_conf->bitchute_player_width;
        }
        if (FreshRSS_Context::$user_conf->bitchute_player_height != '') {
            $this->height = FreshRSS_Context::$user_conf->bitchute_player_height;
        }
    }

    /**
     * Inserts the Bitchute video iframe into the content of an entry, if the entries link points to a Bitchute watch URL.
     *
     * @param FreshRSS_Entry $entry
     * @return mixed
     */
    public function embedBitchuteVideo($entry)
    {
        $this->loadConfigValues();
        $link = $entry->link();

        if ($this->isBitchuteURL($link))
        {
            $nonembed_link = $this->getNonEmbedLink($link);
            $html = $this->getIFrameHtml($link);
            
            $entry->_content($html.'<br/>'.$entry->content());
            $entry->_link($nonembed_link);
            //$entry->_content("derp");
        }
        
        return $entry;
    }

    //Check if the base URL of an entry is a Bitchute URL
    private function isBitchuteURL(string $url): bool 
    {
        return stripos($url, $this->bitchute_url) == true;
    }
    
    //Bitchute likes to link to embed links in it's feeds,
    //This fixes the links to point to the actual video page instead
    private function getNonEmbedLink(string $url)
    {
        return str_replace("/embed","/video",$url);
    }
   
    /**
     * Returns an HTML <iframe> for a given URL for the configured width and height.
     *
     * @param string $url
     * @return string
     */
    private function getIFrameHtml($url)
    {
    
        return '<iframe 
                style="height: ' . $this->height . 'px; width: ' . $this->width . 'px;" 
                width="' . $this->width . '" 
                height="' . $this->height . '" 
                src="' . $url . '" 
                frameborder="0" 
                allowfullscreen></iframe>';
    }
    
    /**
     * Saves the user settings for this extension.
     */
    public function handleConfigureAction()
    {
        $this->registerTranslates();
        $this->loadConfigValues();

        if (Minz_Request::isPost()) {
            FreshRSS_Context::$user_conf->bitchute_player_height = (int)Minz_Request::param('bitchute_height', '');
            FreshRSS_Context::$user_conf->bitchute_player_width = (int)Minz_Request::param('bitchute_width', '');
            FreshRSS_Context::$user_conf->save();
        }
    }
}
