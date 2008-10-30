<?php global $config; ?>
    <div id = "dms-folder-control"></div>
    <script type = "text/javascript">    
        function onLoadChildren(tree) {
            <?php $__xoxoxox=$this->folders;$__dindex=new LoopTagStatus();$__dindex->count=count($__xoxoxox);
                                    for(;$__dindex->index<$__dindex->count;$__dindex->index++){
                                    $__dindex->current=$folder=$__xoxoxox[$__dindex->index]; ?>
                new DMSFolder({
                    label : "<?php echo $folder->name; ?>", 
                    parent : tree.getRoot(),
                    id : <?php echo $folder->id; ?>
                });
            <?php } ?>
        }
    </script>
