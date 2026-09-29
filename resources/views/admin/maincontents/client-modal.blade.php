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
        <h1 class="modal-title fs-5" id="exampleModalLabel">Visit For Checking</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="dashpopup-inner">
        <?php  if($clientDatas){ foreach($clientDatas as $clientData){?>
          <div class="dashpopup-inner-item">
            <div class="visit_img">
              <!-- <img src="<?=$clientData['image']?>" alt="<?=$clientData['client_name']?>"> -->
              <?= Helper::generateLightboxImage($clientData['image'], $clientData['client_name'], '', '', '', '') ?>
            </div>
            <div class="dash_rightinfo">
              <h4><?=$clientData['client_name']?><span><?=$clientData['client_type']?></span></h4>
              <address><?=$clientData['client_address']?></address>
              <div class="meet-thepoerson"><?=$clientData['emp_name']?><span>(<?=$clientData['emp_type']?>)</span>  <time><?=$clientData['time']?></time></div>
              <?php if($clientData['wi_emp_name']){ ?>
              <div class="meetorder_person">
                  <!-- <div class="meetother-with">?=$clientData['emp_name']?>(?=$clientData['emp_type']?>)</div> <span>+</span> <div class="meetother-with">Sudip Kulovi (SO)</div> <span>+</span> <div class="meetother-with">Sandip Sharma(ZM)</div> -->
                  <div class="meetother-with"><?=implode(", ", $clientData['wi_emp_name'])?></div>
              </div>
              <?php } ?>
              <div class="dash_noteinfo">
                <strong>Note:</strong> <?=$clientData['note']?>
              </div>
            </div>
          </div>          
        <?php } } ?> 
        </div>
      </div>
      
    </div>
  </div>