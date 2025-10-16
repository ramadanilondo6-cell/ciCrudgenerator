<div class="footer">
    <div class="pull-right">
    </div>
    <div>
        <strong>Copyright</strong> Genisys Web Solution
    </div>
</div>
</div>
</div>
<!-- Mainly scripts -->
<script src="<?php echo $this->config->item('accet_url') ?>js/jquery-2.1.1.min.js"></script>
<script src="<?php echo $this->config->item('accet_url') ?>js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/dataTables/datatables.min.js"></script>
<!-- Custom and plugin javascript -->
<script src="<?php echo $this->config->item('accet_url') ?>js/app.js"></script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/pace/pace.min.js"></script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/chosen/chosen.jquery.js"></script>
<script type="text/javascript">
    $(".chosen-select").chosen();
</script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/select2/select2.full.min.js"></script>
<script type="text/javascript">
    $(".select2").select2();
</script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
    $(function () {
        window.prettyPrint && prettyPrint();
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd'
        });
    });
</script>
<script src="<?php echo $this->config->item('accet_url') ?>js/plugins/clockpicker/clockpicker.js"></script>
<script type="text/javascript">
    $('.clockpicker').clockpicker();
</script>
<script src="<?php echo $this->config->item('accet_url') ?>css/plugins/moment-develop/min/moment-with-locales.js"></script>
<script src="<?php echo $this->config->item('accet_url') ?>js/bootstrap-datetimepicker.js"></script>
<script type="text/javascript">
    $(function () {
        $(".datetimepicker").datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    });
</script>
<script src="<?php echo $this->config->item('accet_url') ?>js/manage.js"></script>
<script>
    $(document).ready(function(){
        $('.dataTables-example').DataTable({
            pageLength: 25,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                { extend: 'copy'},
                {extend: 'csv'},
                {extend: 'excel', title: 'ExampleFile'},
                {extend: 'pdf', title: 'ExampleFile'},

                {extend: 'print',
                 customize: function (win){
                        $(win.document.body).addClass('white-bg');
                        $(win.document.body).css('font-size', '10px');

                        $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                }
                }
            ]

        });

    });

</script>
</body>
</html>