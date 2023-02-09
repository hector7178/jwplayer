<!-- Page title -->
<div class="page-header">
   <div class="row align-items-center">
      <div class="col-auto">
         <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="<?=PROOT?>/dashboard">Dashboard</a></li>
            <?php foreach($parentFolders as $k => $pfolder): ?>
            <li class="breadcrumb-item <?php if($k == 0) echo 'active'; ?>"><a href="<?=PROOT?>/mydrive/<?=$pfolder['id']?>?driveId=<?=$activeDriveId?><?php if($filter) echo '&filter=1'; ?>"><?=$pfolder['name']?></a></li>
            <?php endforeach; ?>
         </ol>
      </div>
   </div>
</div>
<!-- Content here -->
<!-- Content here -->
<div class="row">
   <div class="col-md-12">
      <div class="card">
         <div id="mydrive"></div>
         <div class="top-bar card-header bg-dark text-light py-2 border-bottom-0" style="    justify-content: space-between;">
            <div>
               <?php if($driveConnection): ?>
               <select class="form-select py-0" style="height: auto;"  id="change-drive-account" >
                  <?php foreach($this->getDriveAccounts(true) as $ak => $ac): ?>
                  <option value="<?=Helper::getFullUrl("mydrive?driveId=$ak")?>" <?php if($ak == $activeDriveId) echo 'selected="selected"'; ?>  ><?=$ac['email']?></option>
                  <?php endforeach; ?>
               </select>
               <?php endif; ?>
            </div>
            <div>
               <span>My drive connection : 
               <?php if($driveConnection): ?>
               <span class="badge bg-success">Success</span> </span>   
               <?php else: ?>
               <span class="badge bg-danger">Failed</span> </span>   
               <?php endif; ?>
            </div>
         </div>
         <div class="card-header" style="    justify-content: space-between;">
            <h3 class="card-title">
               My Drive 
               <span id="activeDriveId" data-id="<?=$activeDriveId?>" class="d-none"></span>
               <span id="folderId" data-id="<?=$folderId?>" class="d-none"></span>
               <?php if(!$driveConnection): ?>
               <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z"></path>
                  <circle cx="12" cy="12" r="9"></circle>
                  <line x1="9" y1="10" x2="9.01" y2="10"></line>
                  <line x1="15" y1="10" x2="15.01" y2="10"></line>
                  <path d="M9.5 15.25a3.5 3.5 0 0 1 5 0"></path>
               </svg>
               <?php else: ?>
               <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z"></path>
                  <circle cx="12" cy="12" r="9"></circle>
                  <line x1="9" y1="9" x2="9.01" y2="9"></line>
                  <line x1="15" y1="9" x2="15.01" y2="9"></line>
                  <path d="M8 13a4 4 0 1 0 8 0m0 0H8"></path>
               </svg>
               <?php endif; ?>
            </h3>
            <div class="btn-list flex-nowrap">
               <button type="button" data-id="bulk" class="btn btn-warning d-none  btn-block import-selected-drive-files <?php if(!$driveConnection) echo 'disabled'; ?>" >
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                     <path stroke="none" d="M0 0h24v24H0z"></path>
                     <path d="M19 18a3.5 3.5 0 0 0 0 -7h-1a5 4.5 0 0 0 -11 -2a4.6 4.4 0 0 0 -2.1 8.4"></path>
                     <line x1="12" y1="13" x2="12" y2="22"></line>
                     <polyline points="9 19 12 22 15 19"></polyline>
                  </svg>
                  Import selected videos
               </button>
               <button type="button" data-toggle="modal" data-target="#upload-file-to-drive" class="btn btn-primary btn-block <?php if(!$driveConnection) echo 'disabled'; ?>" >
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                     <path stroke="none" d="M0 0h24v24H0z"></path>
                     <path d="M7 18a4.6 4.4 0 0 1 0 -9h0a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1"></path>
                     <polyline points="9 15 12 12 15 15"></polyline>
                     <line x1="12" y1="12" x2="12" y2="21"></line>
                  </svg>
                  Upload
               </button>
               <button type="button" class="btn btn-outline-primary btn-block <?php if(!$driveConnection) echo 'disabled'; ?>" data-toggle="modal" data-target="#create-new-folder-modal">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                     <path stroke="none" d="M0 0h24v24H0z"></path>
                     <path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path>
                     <line x1="12" y1="10" x2="12" y2="16"></line>
                     <line x1="9" y1="13" x2="15" y2="13"></line>
                  </svg>
                  New folder
               </button>
            </div>
         </div>
         <div class="px-3 pt-2 text-right">
            <label class="form-check d-inline-block">
            <input class="form-check-input" name="fidf" id="filter-imported-links" type="checkbox"    <?php if($filter) echo 'checked="checked"'; ?>             >
            <span class="form-check-label">Filter imported links</span>
            </label>
         </div>
         <div id="alert-wrap"></div>
         <?php $this->displayAlerts(); ?>
         <div class="table-responsive" id="<?php if(!$isRoot) echo 'drive-exploer-tbl'; ?>">
            <?php if(!$isRoot && !empty($preBackUri)): ?>
            <div class="go-back-bar">
               <a href="<?=$preBackUri?><?php if($filter) echo '&filter=1"'; ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                     <path stroke="none" d="M0 0h24v24H0z"></path>
                     <circle cx="5" cy="12" r="1"></circle>
                     <circle cx="12" cy="12" r="1"></circle>
                     <circle cx="19" cy="12" r="1"></circle>
                  </svg>
               </a>
            </div>
            <?php endif; ?>
            <table class="table card-table table-vcenter text-nowrap datatable">
               <thead>
                  <tr>
                     <th class="w-1 no-sort"><input class="form-check-input m-0 align-middle " id="select_all_drive_files" type="checkbox"></th>
                     <th>Name</th>
                     <th>Status</th>
                     <th>Is shared</th>
                     <th>Last Updated</th>
                     <th class="w-1"></th>
                  </tr>
               </thead>
               <tbody>
                  <?php $link = new Link(); ?>
                  <?php if(!empty($files)): ?>
                  <?php foreach($files as $file):
                     $isFolder = MyDrive::isFolder($file['mimeType']);
                     $existLink = !$isFolder ? $link->search($file['id']) : false;
                     
                     if($filter && $existLink != false) continue;
                     
                     ?>
                  <tr data-id="<?=$file['id']?>">
                     <td class="no-sort ">
                        <?php 
                           $disabled = '';
                           $s = 'select-d-f';
                           if(MyDrive::isFolder($file['mimeType'])) {
                              $disabled = 'disabled';
                              $s ='';
                           } ?>
                        <input class="form-check-input m-0 align-middle sel-f-g <?=$s?> "   type="checkbox" <?=$disabled?> name="selected-drive-files[]" value="<?=$file['id']?>" aria-label="Select invoice">
                     </td>
                     <td>
                        <?php if($isFolder): ?>
                        <a href=" <?=PROOT?>/mydrive/<?=$file['id']?>?driveId=<?=$activeDriveId?><?php if($filter) echo '&filter=1"'; ?>" class="text-reset">
                        <?php else: ?>
                        <a href="javascript:void(0)" style="    cursor: default;" class="text-reset">
                           <?php endif; ?>
                           <?php if($isFolder): ?>
                           <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z"></path>
                              <path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path>
                           </svg>
                           <?php else: ?>
                           <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z"></path>
                              <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                              <line x1="8" y1="4" x2="8" y2="20"></line>
                              <line x1="16" y1="4" x2="16" y2="20"></line>
                              <line x1="4" y1="8" x2="8" y2="8"></line>
                              <line x1="4" y1="16" x2="8" y2="16"></line>
                              <line x1="4" y1="12" x2="20" y2="12"></line>
                              <line x1="16" y1="8" x2="20" y2="8"></line>
                              <line x1="16" y1="16" x2="20" y2="16"></line>
                           </svg>
                           <?php endif; ?>
                           &nbsp;<?=$file['name']?>
                        </a>
                     </td>
                     <td >
                        <?php 
                           if($existLink !== false): ?>
                        <span class=" text-success" data-toggle="tooltip" data-placement="top" title="Imported">
                           <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z"></path>
                              <circle cx="12" cy="12" r="9"></circle>
                              <path d="M9 12l2 2l4 -4"></path>
                           </svg>
                        </span>
                     </td>
                     <?php elseif($isFolder): ?>
                     <span class="badge">--</span> </td>
                     <?php else: ?>
                     <span class="text-secondary" data-toggle="tooltip" data-placement="top" title="Not Imported">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                           <path stroke="none" d="M0 0h24v24H0z"></path>
                           <circle cx="12" cy="12" r="9"></circle>
                           <line x1="9" y1="12" x2="15" y2="12"></line>
                        </svg>
                     </span>
                     </td>
                     <?php endif; ?>
                     <td>
                        <?php if(!$file['shared']): ?>
                        <a href="javascript:void(0)" class="text-secondary change-shared-status" data-val="not-shared" data-toggle="tooltip" data-placement="top" title="Not Shared">
                           <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z"></path>
                              <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                              <circle cx="12" cy="16" r="1"></circle>
                              <path d="M8 11v-4a4 4 0 0 1 8 0v4"></path>
                           </svg>
                        </a>
                        <?php else: ?>
                        <a href="javascript:void(0)" class="text-warning change-shared-status" data-val="shared" data-toggle="tooltip" data-placement="top" title="Shared">
                           <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z"></path>
                              <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                              <circle cx="12" cy="16" r="1"></circle>
                              <path d="M8 11v-5a4 4 0 0 1 8 0"></path>
                           </svg>
                        </a>
                        <?php endif; ?>
                     </td>
                     <td><?=$file['modifiedByMeTime']?></td>
                     <td>
                        <div class="btn-list flex-nowrap">
                           <a href="javascript:void(0)" class="btn btn-vk import-drive-file  btn-square btn-sm <?php if(MyDrive::isFolder($file['mimeType']) || $existLink != false) echo 'disabled '; ?>">
                           import
                           </a>
                           <a href="javascript:void(0)"  class="btn btn-secondary    btn-square btn-sm mcd-file <?php if(MyDrive::isFolder($file['mimeType'])) echo 'disabled '; ?>"    >
                           copy
                           </a>
                           <a href="<?=$existLink != false ? PROOT.'/links/edit/'.$existLink['id'] : 'javascript:void(0)' ?>" class="btn btn-info    btn-square btn-sm <?php if($existLink == false) echo 'disabled'; ?>" target="_blank">
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md m-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                 <path stroke="none" d="M0 0h24v24H0z"></path>
                                 <path d="M11 7h-5a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-5"></path>
                                 <line x1="10" y1="14" x2="20" y2="4"></line>
                                 <polyline points="15 4 20 4 20 9"></polyline>
                              </svg>
                           </a>
                           <?php if(MY_DRIVE_FILE_DELETE_ACTION): ?>
                           <a href="javascript:void(0)" class="text-google  ml-2 del-drive-file" data-toggle="tooltip" data-placement="top" title="delete">
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" style="font-size: 1.3rem;" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                 <path stroke="none" d="M0 0h24v24H0z"></path>
                                 <line x1="4" y1="7" x2="20" y2="7"></line>
                                 <line x1="10" y1="11" x2="10" y2="17"></line>
                                 <line x1="14" y1="11" x2="14" y2="17"></line>
                                 <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                                 <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                              </svg>
                           </a>
                           <?php endif; ?>
                        </div>
                     </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
               </tbody>
            </table>
         </div>
         <?php if(!empty($nextPageToken)): ?>
         <div class="p-3 text-right"> <a href="<?=$_SERVER['REQUEST_URI']?>&nextPageToken=<?=$nextPageToken?>"> <b>Go to next page</b> </a> </div>
         <?php endif; ?>
      </div>
   </div>
</div>
<div class="row df d-none">
   <div class="col-md-9">
      <div class="card">
         <div class="card-body">
            <div class="d-flex mb-3 " style="    justify-content: space-between;">
               <div>
                  <div id="processing-alert" style="display: none;">
                     <div class="spinner-border " style="    vertical-align: middle;" role="status"></div>
                     &nbsp;
                     <span class="">processing...</span>
                  </div>
               </div>
               <div class="">
                  <a href="javascript:void(0)" class="text-danger " id="clear-logs">clear logs</a>                   
               </div>
            </div>
            <ul id="mi-response" class="list-group-flush" style="    list-style-type: decimal-leading-zero;">
               </li>
            </ul>
         </div>
      </div>
   </div>
   <div class="col-md-3">
      <div class="card">
         <div class="card-body">
            <ul class="list-group list-group-flush">
               <li class="list-group-item"> <b>Total Links : <span class="float-right t-links">0</span></b> </li>
               <li class="list-group-item text-warning"> <b>Pending : <span class="float-right p-links">0</span> </b> </li>
               <li class="list-group-item text-success"> <b>Success : <span class="float-right s-links">0</span></b> </li>
               <li class="list-group-item text-danger"> <b>Failed : <span class="float-right f-links">0</span></b> </li>
            </ul>
         </div>
      </div>
   </div>
</div>
<?php if($driveConnection): ?>
<div class="modal modal-blur fade" id="create-new-folder-modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
         <form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
            <div class="modal-body">
               <div class="modal-title">New folder</div>
               <div class="">
                  <input type="text" class="form-control" name="folder" placeholder="Enter folder name">
               </div>
               <input type="text" name="type" value="new-folder" hidden>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-link link-secondary mr-auto" data-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" >Create</button>
            </div>
         </form>
      </div>
   </div>
</div>
<div class="modal modal-blur fade" id="del-drive-file" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <div class="modal-title">Are you sure?</div>
            <div>If you proceed, you will lose all your personal data.</div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-link link-secondary mr-auto" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Yes, delete all my data</button>
         </div>
      </div>
   </div>
</div>
<!-- <div class="modal modal-blur fade show" id="" tabindex="-1" role="dialog" style="display: block; padding-right: 17px;" aria-modal="true"> -->
<div class="modal modal-blur fade" id="upload-file-to-drive" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Remote URL Upload</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z"/>
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
               </svg>
            </button>
         </div>
         <div class="modal-body">
            <div id="console" style="display: none;">
               <!-- <span class="Typewriter__wrapper">Google Drive Uploader [Version 1.0]<br>2021 Developed by CodySeller. All rights reserved.</span>
                  <span class="Typewriter__cursor">|</span> -->
               <!--
                  <p><span class="pre-symbool">$</span> Attempt to upload 1<sup>st</sup> file... <span class="text-success">OK</span> </p>
                  <p><span class="pre-symbool">$</span> Uploading... <span class="text-warning">76%</span> </p>
                  <p><span class="pre-symbool">$</span> File is uploaded successfully !</p>
                  <p><span class="pre-symbool">$</span> ...</p>
                  
                  <p><span class="pre-symbool">$</span> Attempt to upload 2<sup>nd</sup> file... <span class="text-danger">FAILED</span> </p>
                  <p class="text-danger"><span class="pre-symbool">$</span> we're sorry. we can not access this file.</p>
                  <p><span class="pre-symbool">$</span> Upload failed !</p>
                  <p><span class="pre-symbool">$</span> ...</p>
                  
                  <p><span class="pre-symbool">$</span> Attempt to upload 1<sup>st</sup> file... <span class="text-success">OK</span> </p>
                  <p><span class="pre-symbool">$</span> Uploading... <span class="text-warning">76%</span> </p>
                  <p><span class="pre-symbool">$</span> File is uploaded successfully !</p>
                  <p><span class="pre-symbool">$</span> ...</p> -->
               <div class="anchor">anchor</div>
            </div>
            <form action="#" id="drive-upload-form">
               <div class="form-group mb-3">
                  <textarea class="form-control"  name="" id="link-list" rows="6" placeholder="Enter one URL per line">http://files.xteenhat.com/video/video.mp4</textarea>
               </div>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="upload-to-drive" >Upload</button>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<div class="modal modal-blur fade" id="mcd-file-modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <div  class="resp-wrap d-none">
               <div class="alrt"></div>
               <div class="mb-3 rpt d-none">
                  <label class="form-label">Copied File ID</label>
                  <input type="text" id="copied-file-id" class="form-control" name="" placeholder="" value="" readonly="">
               </div>
            </div>
            <div class="df-wrap">
               <div class="modal-title">Select account</div>
               <div id="selectdFileId" class='d-none'></div>
               <select class="form-select" id="selectedDriveAcc">
                  <?php foreach($driveAccounts as $daccK => $daccV):
                     if($daccK == $activeDriveId) continue;
                     ?>
                  <option value="<?=$daccK?>"><?=$daccV['email']?></option>
                  <?php endforeach; ?>
               </select>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-link link-secondary mr-auto" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="make-a-drive-file-copy">Make a copy</button>
         </div>
      </div>
   </div>
</div>