<?php
CroogoNav::add('Locales Search', 
            array(
                'title' => __d('localization_words','Localization'),
                'icon' => array('globe', 'large'),
                'weight' => 30,
                'url' => array(
                    'admin' => true,
                    'plugin' => 'localization_words',
                    'controller' => 'localizations',
                    'action' => 'index'
                )
            )
        );
?>
