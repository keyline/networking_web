<style>
  .lightbox .lb-nav {
      display: none !important;
  }
</style>
<style>
  /* modal css */
  .modal.drawer {
      display: flex !important;
      pointer-events: none;
  }

  .modal.drawer * {
      pointer-events: none;
  }

  .modal.drawer .modal-dialog {
      margin: 0px;
      display: flex;
      flex: auto;
      transform: translate(25%, 0);
  }

  .modal.drawer .modal-dialog .modal-content {
      border: none;
      border-radius: 0px;
  }

  .modal.drawer .modal-dialog .modal-content .modal-body {
      overflow: auto;
  }

  .modal.drawer.show {
      pointer-events: auto;
  }

  .modal.drawer.show * {
      pointer-events: auto;
  }

  .modal.drawer.show .modal-dialog {
      transform: translate(0, 0);
  }

  .modal.drawer.right-align {
      flex-direction: row-reverse;
  }

  .modal.drawer.left-align:not(.show) .modal-dialog {
      transform: translate(-25%, 0);
  }
</style>
<div class="modal-dialog">
    <div class="modal-content dashboard_all_popup">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Today Order</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="dashpopup-inner">
          <?php  if($orderDatas){ foreach($orderDatas as $orderData){?>
          <div class="dashpopup-inner-item">
            <div class="visit_img">
              <!-- <img src="<?=$orderData['image']?>" alt="<?=$orderData['client_name']?>"> -->
              <?= Helper::generateLightboxImage($orderData['image'], $orderData['client_name'], '', '', '', '') ?>
            </div>
            <div class="dash_rightinfo">
              <h4 class="storename"><span><?=$orderData['order_no']?></span><?=$orderData['client_name']?><span><?=$orderData['client_type']?></span></h4>
              <address><?=$orderData['client_address']?></address>
              <div class="meet-thepoerson"><?=$orderData['emp_name']?><span>(<?=$orderData['emp_type']?>)</span>  <time><?=$orderData['time']?></time></div>
              <div class="dash_totalorder"><i class="fa-solid fa-indian-rupee-sign"></i> <?=$orderData['net_total']?></div>
            </div>
          </div> 
          <?php } } ?>         
        </div>
      </div>
    </div>
  </div>