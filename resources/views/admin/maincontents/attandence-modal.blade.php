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
        <h1 class="modal-title fs-5" id="exampleModalLabel">Employee Punched</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php  if($attnDatas){ foreach($attnDatas as $attnData){?>
        <div class="dashpopup-inner">
          <div class="dashpopup-inner-item">
            <div class="visit_img">
              <!-- <img src="<?=$attnData['image']?>" alt="<?=$attnData['name']?>"> -->
              <?= Helper::generateLightboxImage($attnData['image'], $attnData['name'], '', '', '', '') ?>
            </div>
            <div class="dash_rightinfo">
              <h4><?=$attnData['name']?><span><?=$attnData['emp_type']?></span></h4>
              <address><?=$attnData['address']?></address>
              <time><?=$attnData['time']?></time>
            </div>
          </div>          
        </div>
        <?php } } ?>
      </div>
      
    </div>
  </div>