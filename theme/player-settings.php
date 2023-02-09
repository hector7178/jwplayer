<!-- Page title -->
<div class="page-header">
   <div class="row align-items-center">
      <div class="col-auto">
         <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="<?=PROOT?>/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?=PROOT?>/settings/general">settings</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0)">Video</a></li>
         </ol>
      </div>
   </div>
</div>
<!-- Content here -->
<div class="row">
   <div class="col-md-9">
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">General settings</h3>
         </div>
         <div class="card-body">
            <?=$this->displayAlerts()?>
            <form action="<?=$_SERVER['REQUEST_URI']?>" method="post" enctype="multipart/form-data" >
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Video player
                  </label>
                  <div class="col">
                     <select class="form-select" name="player">
                        <option value="jw" <?php if($this->config['player'] == 'jw') echo 'selected="selected"'; ?> >JW Player</option>
                        <option value="plyr" <?php if($this->config['player'] == 'plyr') echo 'selected="selected"'; ?> >Plyr.io</option>
                     </select>
                     <small class="form-hint">Select your player type</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">JW player license
                  </label>
                  <div class="col">
                     <input type="text" class="form-control" placeholder="https://content.jwplatform.com/libraries/Jq6HIbgz...."  name="jw_license" value="<?=$this->config['jw_license']?>" >
                     <small class="form-hint">Add your jw player license</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Video page slug
                  </label>
                  <div class="col">
                     <input type="text" class="form-control" placeholder="video"  name="playerSlug" value="<?=$this->config['playerSlug']?>" >
                     <small class="form-hint">Video page slug</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Video preloader
                  </label>
                  <div class="col">
                     <label class="form-check form-switch">
                     <input class="form-check-input" name="v_preloader" type="checkbox" <?php if($this->config['v_preloader'] == 1) echo 'checked="checked"'; ?>>
                     <span class="form-check-label"></span>
                     </label>          <small class="form-hint">Enable/ disable video preloader animation</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Adblock detecter
                  </label>
                  <div class="col">
                     <label class="form-check form-switch">
                     <input class="form-check-input" name="isAdblocker" type="checkbox" <?php if($this->config['isAdblocker'] == 1) echo 'checked="checked"'; ?>>
                     <span class="form-check-label"></span>
                     </label>          <small class="form-hint">Enable/ disable adblock detecter</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Auto play
                  </label>
                  <div class="col">
                     <label class="form-check form-switch">
                     <input class="form-check-input" name="autoPlay" type="checkbox" <?php if($this->config['autoPlay'] == 1) echo 'checked="checked"'; ?>>
                     <span class="form-check-label"></span>
                     </label>          <small class="form-hint">Enable/ disable video auto play option (video will be muted)</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Loadbanalcer Rand
                  </label>
                  <div class="col">
                     <label class="form-check form-switch">
                     <input class="form-check-input" name="streamRand" type="checkbox" <?php if($this->config['streamRand'] == 1) echo 'checked="checked"'; ?>>
                     <span class="form-check-label"></span>
                     </label>          <small class="form-hint">If you enabled this option, we will select loadbanalcer (stream) servers randomly. and it not disaply in server list.</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Disabled qulities
                  </label>
                  <div class="col">
                     <div>
                        <label class="form-check form-check-inline">
                        <input class="form-check-input" name="dq[]" value="360" type="checkbox" <?php if(in_array(360,$disabledQulities)) echo 'checked=""'; ?>       >
                        <span class="form-check-label">360p</span>
                        </label>
                        <label class="form-check form-check-inline">
                        <input class="form-check-input" name="dq[]"  value="480" type="checkbox" <?php if(in_array(480,$disabledQulities)) echo 'checked=""'; ?>>
                        <span class="form-check-label">480p</span>
                        </label>
                        <label class="form-check form-check-inline">
                        <input class="form-check-input" name="dq[]"  value="720" type="checkbox" <?php if(in_array(720,$disabledQulities)) echo 'checked=""'; ?>>
                        <span class="form-check-label">720p</span>
                        </label>
                        <label class="form-check form-check-inline">
                        <input class="form-check-input" name="dq[]"  value="1080" type="checkbox" <?php if(in_array(1080,$disabledQulities)) echo 'checked=""'; ?>>
                        <span class="form-check-label">1080p</span>
                        </label>
                        <small class="form-hint">Disable qulity formats in google drive videos</small>
                     </div>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Default video
                  </label>
                  <div class="col">
                     <input type="url" class="form-control" placeholder="https://cdn1.kccmacs.lk/files/videos/no-content.mp4"  name="default_video" value="<?=$this->config['default_video']?>" >
                     <small class="form-hint">If some link is broken, this video will be play automatically</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Default banner
                  </label>
                  <div class="col">
                     <input type="url" class="form-control" placeholder="https://mydomain.com/uploads/default-banner.png"  name="default_banner" value="<?=$this->config['default_banner']?>" >
                     <small class="form-hint">Default poster image in player</small>
                  </div>
               </div>
               <div class="form-footer text-right">
                  <button type="submit" class="btn btn-primary">Save</button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>