<?php
$this->extend('/Common/admin_index');
$this->start('actions');
	echo $this->Croogo->adminAction(
		__d('localization_words', 'Check'),
		array('action' => 'add')
	);
$this->end();
?>
<table class="table table-striped">
<?php
    $tableHeaders = $this->Html->tableHeaders(array(
                    __d('localization_words', 'Row'),
                    __d('localization_words', 'Plugins'),
                    __d('localization_words', 'Actions')
                )
            );
?>
    <thead>
        <?php echo $tableHeaders;?>
    </thead>
<?php
$rows = array();
$i = 1;
foreach ($plugins as $pluginAlias => $pluginData) {
    
    $actions = $this->Croogo->adminRowAction(
                __d('localization_words', 'Update Domains'), 
                array('action' => 'update', $pluginAlias)
            );
    
    $rows[] = array(
        $i++,
        $pluginData['name'],
        $actions
    );
}
    echo $this->Html->tableCells($rows);
?>
</table>