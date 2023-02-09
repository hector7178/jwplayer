<!-- Page title -->
<div class="page-header">
   <div class="row align-items-center">
      <div class="col-auto">
         <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="<?=PROOT?>/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?=PROOT?>/settings/general">Settings</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0)">General</a></li>
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
                  <label class="form-label col-3 col-form-label">Logo</label>
                  <div class="col">
                     <input type="file" class="form-control" name="logo" >
                     <input type="text" name="logo" id="logoVal" value="<?=$this->config['logo']?>" hidden>
                     <?php if(!empty($this->config['logo'])): ?>
                     <br>  
                     <img src="<?=PROOT?>/uploads/<?=$this->config['logo']?>" id="logoImg" height="50" alt="logo-img">
                     <br>
                     <a href="javascript:void(0)" class="text-danger" id="removeLogo" >remove</a>
                     <?php endif; ?>
                     <small class="form-hint">Logo image for Player/ admin panel</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Favicon</label>
                  <div class="col">
                     <input type="file" class="form-control" name="favicon" >
                     <input type="text" name="favicon" id="favVal" value="<?=$this->config['favicon']?>" hidden>
                     <?php if(!empty($this->config['favicon'])): ?>
                     <br>  
                     <img src="<?=PROOT?>/uploads/<?=$this->config['favicon']?>" id="favIco" height="16" alt="favicon-img">
                     <br>
                     <a href="javascript:void(0)" class="text-danger" id="removeFav" >remove</a>
                     <?php endif; ?>
                     <small class="form-hint">Favicon for Player/ admin panel</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Subtitle Languages
                  </label>
                  <div class="col">
                     <?php
                        $sublist ='';
                            if (!empty(trim($this->config['sublist']))) {
                              $sublist = implode(', ', json_decode($this->config['sublist'], true));
                            }?>
                     <textarea class="form-control" name="sublist" placeholder="Sinhala, English, Hindi"  rows="5" ><?=$sublist?></textarea>            
                     <small class="form-hint">Separate each language by comma ( , )</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Dark theme
                  </label>
                  <div class="col">
                     <label class="form-check form-switch">
                     <input class="form-check-input" name="dark_theme" type="checkbox" <?php if($this->config['dark_theme'] == 1) echo 'checked="checked"'; ?>>
                     <span class="form-check-label"></span>
                     </label>          <small class="form-hint">Enable/ disable dark theme mode</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Timezone
                  </label>
                  <div class="col">
                     <select class="form-select" name="timezone">
                     <?php $tzlist = Helper::getTimeZoneList();
                        foreach ($tzlist as $tz) {
                            $selected = ($this->config['timezone'] == $tz ) ? 'selected' : '';
                            echo "<option value='{$tz}' {$selected}>{$tz}</option>";
                        }
                            ?>
                     </select>
                     <small class="form-hint">Select your timezone</small>
                  </div>
               </div>
               <div class="form-group mb-3 row">
                  <label class="form-label col-3 col-form-label">Rename Alt sources
                  </label>
                  <div class="col">
                     <div class="input-group mb-2">
                        <span class="input-group-text">
                        OneDrive
                        </span>
                        <input type="text" class="form-control" name="altR[onedrive][n]" placeholder="my custom one drive" value="<?php if(isset($altR['onedrive']['n'])) echo $altR['onedrive']['n']; ?>"      >
                     </div>
                     <div class="input-group mb-2">
                        <span class="input-group-text">
                        Ok.ru
                        </span>
                        <input type="text" class="form-control" name="altR[okru][n]" placeholder="my custom okru" value="<?php if(isset($altR['okru']['n'])) echo $altR['okru']['n']; ?>">
                     </div>
                     <div class="input-group mb-2">
                        <span class="input-group-text">
                        GPhoto
                        </span>
                        <input type="text" class="form-control" name="altR[gphoto][n]" placeholder="my custom gphoto" value="<?php if(isset($altR['gphoto']['n'])) echo $altR['gphoto']['n']; ?>">
                     </div>
                     <div class="input-group mb-2">
                        <span class="input-group-text">
                        Direct
                        </span>
                        <input type="text" class="form-control" name="altR[direct][n]" placeholder="my custom direct server" value="<?php if(isset($altR['direct']['n'])) echo $altR['direct']['n']; ?>">
                     </div>
                     <small class="form-hint">Enable/ disable dark theme mode</small>
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