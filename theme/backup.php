<!-- Page title -->
<div class="page-header">
   <div class="row align-items-center">
      <div class="col-auto">
         <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="<?=PROOT?>/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0)">Backup</a></li>
         </ol>
      </div>
   </div>
</div>
<!-- Content here -->
<div class="row">
   <div class="col-md-7">
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">Google drive backup</h3>
         </div>
         <div class="card-body">
            <?=$this->displayAlerts()?>
            <?php if(!empty($removedBackupDrives)): ?>
            <?php foreach($removedBackupDrives as $bk => $bv): ?>
            <div class="alert alert-danger"> <b><?=$bv?></b> backup account has beed deleted. Do you need delete permanently , releated links with this drive ?
               &nbsp;
               <a href="javascript:void(0)" data-url="<?=PROOT?>/settings/backup/del-backup-links/<?=$bk?>" class="btn btn-danger btn-sm del-all-backup-links">Yes, delete that all backup links</a>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            <form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
               <div class="mb-3">
                  <label class="form-label">Select drive accounts</label>
                  <select id="select-tags-advanced" name="backup_drives[]" class="form-select" multiple>
                     <?php foreach($driveAccounts as $ak => $ac): ?>
                     <option value="<?=$ak?>" <?=!empty($ac['is_backup'])?'selected':''?>  ><?=$ac['email']?></option>
                     <?php endforeach; ?>
                  </select>
                  <small class="form-hint">
                  Click input field to Select your backup google drive accounts 
                  </small>
               </div>
               <div class="mb-3">
                  <label class="form-check">
                  <input class="form-check-input" name="auto_drive_backup" type="checkbox" <?php if($isAutoBackup) echo 'checked=""';  ?> >
                  <span class="form-check-label">Enable/ Disabled auto backup</span>
                  </label>
                  <small class="form-hint">
                  If you enbaled auto backup, we will automatically create backup file into backup drives after you inserted new link/s.
                  </small>
               </div>
               <div class="text-right">
                  <button type="submit" class="btn btn-primary" >Save</button>
               </div>
            </form>
         </div>
      </div>
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">Database backup</h3>
         </div>
         <div class="card-body">
            <?=$this->displayAlerts()?>
            <div class="row align-items-center">
               <div class="col-auto">
                  <p> <b>Last backup</b> : <br> <?=Helper::formatDT($this->config['last_backup'])?> </p>
               </div>
               <div class="col-auto ml-auto d-print-none">
                  <!-- <span class="d-none d-sm-inline">
                     <a href="#" class="btn btn-secondary">
                       New view
                     </a>
                     </span> -->
                  <a href="<?=PROOT?>/settings/backup?i=1" class="btn btn-primary ml-3 d-none d-sm-inline-block">
                  Get backup
                  </a>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>