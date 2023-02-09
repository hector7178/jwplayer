<!-- Page title -->
<div class="page-header">
   <div class="row align-items-center">
      <div class="col-auto">
         <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="<?=PROOT?>/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?=PROOT?>/settings/general">settings</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0)">GDrive Accounts</a></li>
         </ol>
      </div>
   </div>
</div>
<!-- Content here -->
<div class="row">
   <div class="col-md-7">
      <div class="card">
         <?php $this->displayAlerts(); ?>
         <div class="card-header">
            <h3 class="card-title">Google drive accounts</h3>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-vcenter card-table">
                  <thead>
                     <tr>
                        <th>Account</th>
                        <th>Status</th>
                        <th>Active/ pause</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach($this->getDriveAccounts() as $dk => $driveAcc): ?>
                     <tr>
                        <td class=""><?=$driveAcc['email']?></td>
                        <td class="text-muted">
                           <?php if($driveAcc['is_paused'] != 1): ?>
                           <span class="badge bg-green-lt">active</span>
                           <?php else: ?>
                           <span class="badge bg-yellow-lt">paused</span>
                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <?php if($driveAcc['is_paused'] != 1): ?>
                           <a href="<?=PROOT?>/settings/gdrive-accounts/status/<?=$dk?>" class="text-gray ml-2"  data-toggle="tooltip" data-placement="top" title="Pasue">
                              <svg class="icon" width="1em" height="1em" viewBox="0 0 20 20"  style="font-size:1.3rem"  fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M7.5 5.5A1.5 1.5 0 019 7v6a1.5 1.5 0 01-3 0V7a1.5 1.5 0 011.5-1.5zm5 0A1.5 1.5 0 0114 7v6a1.5 1.5 0 01-3 0V7a1.5 1.5 0 011.5-1.5z"></path>
                              </svg>
                           </a>
                           <?php else: ?>
                           <a href="<?=PROOT?>/settings/gdrive-accounts/status/<?=$dk?>" class="text-warning ml-2"  data-toggle="tooltip" data-placement="top" title="Active">
                              <svg class="icon icon-md" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M13.596 10.697l-6.363 3.692c-.54.313-1.233-.066-1.233-.697V6.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 010 1.393z"></path>
                              </svg>
                           </a>
                           <?php endif; ?>
                           <a href="javascript:void(0)" data-url="<?=PROOT?>/settings/gdrive-accounts/del/<?=$dk?>" class="text-danger ml-2 del-gdrive-acc"  >
                              <svg class="icon " width="1em" style="font-size:1.3rem" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M7.5 7.5A.5.5 0 018 8v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm2.5 0a.5.5 0 01.5.5v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm3 .5a.5.5 0 00-1 0v6a.5.5 0 001 0V8z"></path>
                                 <path fill-rule="evenodd" d="M16.5 5a1 1 0 01-1 1H15v9a2 2 0 01-2 2H7a2 2 0 01-2-2V6h-.5a1 1 0 01-1-1V4a1 1 0 011-1H8a1 1 0 011-1h2a1 1 0 011 1h3.5a1 1 0 011 1v1zM6.118 6L6 6.059V15a1 1 0 001 1h6a1 1 0 001-1V6.059L13.882 6H6.118zM4.5 5V4h11v1h-11z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
            <small class="form-hin mt-3">* If some drive account is broken, pause it here. after we will load links from active drives as more faster.</small>
         </div>
      </div>
   </div>
</div>