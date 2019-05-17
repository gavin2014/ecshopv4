<!-- $Id: sitemap.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<div class="main-div">
<p style="padding: 0 10px"><?php echo $this->_var['lang']['license_notice']; ?></p>
</div>
<div class="main-div">
<table width="100%">
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_shopex']; ?></td>
    <td></td>
</tr>
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_shopex_id']; ?></td>
    <td><?php echo $this->_var['certi']['passport_uid']; ?></td>
</tr>
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_certificate']; ?></td>
    <td></td>
</tr>
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_node_id']; ?></td>
    <td><?php echo $this->_var['certi']['node_id']; ?></td>
</tr>
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_certificate_id']; ?></td>
    <td>
    <?php echo $this->_var['certi']['certificate_id']; ?>
    </td>
</tr>
<tr>
    <td></td>
    <td>
    <form method="POST" action="" enctype="multipart/form-data" id="certificateForm" name="certificateForm">
    <?php if (! $this->_var['certi']['certificate_id']): ?>
    <input type="button" class="button" onclick="get_certificate();" value="<?php echo $this->_var['lang']['yunqi_certicate']; ?>"/>
    <?php else: ?>
    <input type="button" value="<?php echo $this->_var['lang']['download_license']; ?>" class="button" onclick="certi('download');"/>&nbsp;&nbsp;
    <input type="button" value="<?php echo $this->_var['lang']['delete_certificate']; ?>" class="button" onclick="certi('delete');"/> <span style="color:red;">删除证书后，将无法使用“云起物流”，“天工收银”，需要重新激活系统才能使用！</span>
    <?php endif; ?>
    </form>
    </td>
    <td>
    </td>
</tr>

</table>
</div>

<div class="main-div">
<table width="100%">
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_bindrelation']; ?></td>
    <?php if (! $this->_var['is_bind']): ?><td width="300px"><a href="certificate.php?act=apply_bindrelation" target="_blank" class="button"><?php echo $this->_var['lang']['apply_bindrelation']; ?></a></td><?php endif; ?>
    <td><a href="certificate.php?act=accept_bindrelation" target="_blank" class="button"><?php echo $this->_var['lang']['accept_bindrelation']; ?></a></td>
    <td></td>
</tr>
</table>
</div>
<div class="main-div">
    <table width="100%">
        <tr>
            <td class="label"><?php echo $this->_var['lang']['label_bindrelation_crm']; ?></td>
            <?php if ($this->_var['is_bind_crm'] != true): ?><td width="300px"><a href="certificate.php?act=apply_bindrelation" target="_blank" class="button"><?php echo $this->_var['lang']['apply_bindrelation']; ?></a></td><?php endif; ?>
            <td><a href="certificate.php?act=accept_bindrelation" target="_blank" class="button"><?php echo $this->_var['lang']['accept_bindrelation']; ?></a></td>
            <td>支持多平台多店铺的会员统一管理，短信营销，数据分析等，<a href="http://yunqi.shopex.cn/products/crm" target="_blank">点击了解详情</a></td>
            <td></td>
        </tr>
        <?php if ($this->_var['is_bind_crm'] == true): ?>
        <tr>
            <td class="label"></td>
            <td>已同步订单：<font color="green"><?php echo $this->_var['bind_crm_order_push']; ?></font></td>
            <td>未同步订单：<font color="#8b0000"><?php echo $this->_var['bind_crm_order_push_no']; ?></font></td>
        </tr>
        <tr>
            <td class="label"></td>
            <td>已同步会员：<font color="green"><?php echo $this->_var['bind_crm_member_push']; ?></font></td>
            <td>未同步会员：<font color="#8b0000"><?php echo $this->_var['bind_crm_member_push_no']; ?></font></td>
        </tr>
        <?php endif; ?>
    </table>
</div>
<div class="main-div">
<table width="100%">
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_my_version']; ?></td>
    <td><?php echo $this->_var['message']; ?></td>
    <td></td>
</tr>
</table>
</div>
<script language="JavaScript">
function certi(act){
    var cform = document.getElementById('certificateForm');
    if(act == 'delete'){
        cform.action='certificate.php?act=delete';
        if(confirm('删除证书后，将无法使用“云起物流”，“天工收银”，需要重新激活系统才能使用！')){
            cform.submit();
        }
    }else if(act =='download'){
        cform.action='certificate.php?act=download';
        cform.submit();
    }
}
</script>
<?php echo $this->fetch('pagefooter.htm'); ?>