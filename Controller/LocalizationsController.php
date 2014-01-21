<?php
APP::uses('Folder', 'Utility');
APP::uses('File', 'Utility');
App::uses('LocalizationWordsAppController', 'LocalizationWords.Controller');
/**
 * Localizations Controller
 *
 */
class LocalizationsController extends LocalizationWordsAppController {

    public $langs = array('eng', 'per');
    
    public function beforeFilter() {
        parent::beforeFilter();
    }
    //------------------------------------------------------------
    public function admin_index() {
        $pluginsObj = new CroogoPlugin();
        $plugins = $pluginsObj->plugins();
        $this->set(compact('plugins'));
    }
    //------------------------------------------------------------
    /**
     * search for locale keywords and save and update plugin domain (plugin_name.po)
     * @param String $pluginAlias
     */
    public function admin_update($pluginAlias){
        $this->set('title_for_layout', __d('localization_words','Update Locales'));
        $this->autoRender = false;
        
        $files = $this->__getPluginFiles($pluginAlias);
        $keywords = $this->__getMsgids($files);
        $domains = $this->__getPluginDomains($pluginAlias);
        $this->__saveDomainKeywords($domains, $keywords, $pluginAlias);
        $this->Session->setFlash(__d('localization_words', 'Translation files successfuly updated.'),'default', array('class' => 'success'));
        $this->redirect(array('admin'=>true, 'action' =>'index'));
    }
    //------------------------------------------------------------
    /**
     * save all keywords in all plugin domains
     * @param Array $domains
     * @param Array $keywords
     */
    private function __saveDomainKeywords($domains, $keywords, $pluginAlias) {
        foreach ($domains as $domain) {
            $domain = new File($domain);
            $domainContents = $domain->read();
            $domainName = Inflector::underscore($pluginAlias);
            $data = '';
            foreach ($keywords[$domainName] as $msgid => $options) {
                foreach ($options['fileAddress'] as $key => $address) {
                    $data .= '#Line '.$options['lineNumber'][$key].' '.$address."\n";
                }
                // اگر قبلا ترجمه شده همان را قرار بده
                $translated = $this->__getOldMsgstr($domainContents, $msgid);
                $data .= 'msgid "'. $msgid .'"'."\n";
                $data .= 'msgstr "'.$translated.'"'."\n\n";
                $data .= '#===================================================================================================='."\n";
            }
            $domain->write('');
            $domain->write($data, 'a');
            $domain->close();
        }        
    }
    //------------------------------------------------------------
    /**
     * get old translated of msgid ======= [eshtebah kar mikonad, chon msgstr haye filehaye gheyr az lang jari ra pak mikonad]
     * @param File String $domainContents
     * @param String $msgid
     * @return String
     */
    private function __getOldMsgstr($content, $msgid) {
        
        $pattern = '/msgid[\s*]\"'.$msgid.'\"[^msgstr.*]msgstr[\s*]\"([^\".]*)\"/';
        preg_match($pattern, $content, $matches);
        if(!empty($matches[1])){
            return $matches[1];
        }
 
        return '';
    }
    //------------------------------------------------------------
    /**
     * get all plugin files
     * @param String Plugin Alias
     * @return Array files
     */
    private function __getPluginFiles($pluginAlias) {
        $dir = new Folder(CakePlugin::path($pluginAlias));
        $files = $dir->findRecursive('.*\.(php|ctp)');
        return $files;
    }
    //------------------------------------------------------------
    /**
     * get list of domains keywords
     * @param Array $files
     * @return Array keywords or msgid
     */
    private function __getMsgids($files) {
        $keywords = array();
        
        $space = '[\s]*';
        $coat = '[\'|\"]';
        $domainPattern = $space.$coat.'([\w_-\s,]+)'.$coat.$space;
        $msgidPattern = $space.$coat.'([\"\'\w_-\s,]+)'.$coat.$space;        
        
        foreach($files as $file){
            
            $file = new File($file);
            $file->open();
            $handle = $file->handle;
            $lineNumber = 1;
            while (!feof($handle)){
                $buffer = fgets($handle, 4096);
                preg_match_all("/__d$space\($domainPattern,$msgidPattern\)/", $buffer, $matches);
                if(!empty($matches[2])){
                    $keywords[$matches[1][0]][$matches[2][0]]['fileAddress'][] = $file->pwd() ;
                    $keywords[$matches[1][0]][$matches[2][0]]['lineNumber'][] = $lineNumber ;
                }
                $lineNumber++;
            }
            $file->close();
        }
        return $keywords;
    }
    //------------------------------------------------------------
    /**
     * get list of plugin domains path
     * @param String $pluginAlias
     * @return Array domains
     */
    private function __getPluginDomains($pluginAlias) {
        $localePath = new Folder(CakePlugin::path($pluginAlias).'Locale');
        //if there is not locale folder create it with content
        if(!$localePath->pwd()){
            $this->createLocalesPath($pluginAlias);
        }
        
        $domains = $localePath->findRecursive(Inflector::underscore($pluginAlias).'\.po');
        //if there is not any .po file create it
        if(empty($domains))
            $domains = $this->createDomains($pluginAlias);
        
        return $domains;
    }
    //------------------------------------------------------------
    /**
     * create Locales Folder
     * @param type $pluginAlias
     * @param type $langs
     */
    public function createLocalesPath($pluginAlias, $langs = array()) {
        if(!empty($langs))
            $locales = $langs;
        else
            $locales = $this->langs;
        
        $localeFolder = new Folder();
        foreach ($locales as $locale) {
            $path = CakePlugin::path($pluginAlias).'Locale'.DS.$locale.DS.'LC_MESSAGES';
            $localeFolder->create($path);
        }
    }
    //------------------------------------------------------------
    /**
     * create domains file (.po)
     * @param type $pluginAlias
     * @param type $langs
     */
    public function createDomains($pluginAlias, $langs = array()) {
        if(!empty($langs))
            $locales = $langs;
        else
            $locales = $this->langs;
        
        $domains = array();
        foreach ($locales as $locale) {
            $file = CakePlugin::path($pluginAlias).'Locale'.DS.$locale.DS.'LC_MESSAGES'.DS.(Inflector::underscore($pluginAlias)).'.po';
            $domainFile = new File($file, true, 0644);
            array_push($domains, $domainFile->pwd());
        }
        return $domains;
    }
    //------------------------------------------------------------
}
