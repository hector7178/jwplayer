<!-- Page title -->
<div class="page-header">
   <div class="row align-items-center">
      <div class="col-auto">
         <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="<?=PROOT?>/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?=PROOT?>/links/all">Links</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0)">Edit Link</a></li>
         </ol>
      </div>
   </div>
</div>
<!-- Content here -->
<form action="<?=$_SERVER['REQUEST_URI']?>" method="post" enctype="multipart/form-data" >
   <div class="row">
      <div class="col-md-8">
         <div class="card">
            <div id="console2"></div>
            <div class="card-header " style="    justify-content: space-between;">
               <h3 class="card-title">Edit link</h3>
               <a href="<?=Helper::getPlyrLink($link['slug'])?>" target="_blank" class="text-dark">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                     <path stroke="none" d="M0 0h24v24H0z"></path>
                     <path d="M11 7h-5a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-5"></path>
                     <line x1="10" y1="14" x2="20" y2="4"></line>
                     <polyline points="15 4 20 4 20 9"></polyline>
                  </svg>
               </a>
            </div>
            <div class="card-body">
               <?php $this->displayAlerts(); ?>
               <div id="alert-wrap"></div>
               <div class="form-group mb-3 ">
                  <label class="form-label">File Title</label>
                  <div>
                     <input type="text" class="form-control" name="title" value="<?=$link['title']?>"  placeholder="Enter file name">
                  </div>
               </div>
               <div class="form-group mb-3 ">
                  <label class="form-label">Main Link*</label>
                  <div>
                     <div class="input-group mb-2">
                        <button class="btn  btn-secondary"  type="button"  data-toggle="tooltip" data-placement="top" title="<?=$link['type']?>">
                        <img src="<?=Helper::getIcon($link['type'])?>" height="15" alt="">
                        </button>
                        <span class="input-group-text">
                        <?=Helper::getStatus($link['status'])?> 
                        </span>
                        <input type="text" class="form-control" name="main_link" value="<?=$link['main_link']?>" placeholder="Enter your main link" required>
                     </div>
                     <small class="form-hint">Supported sources: google drive, google photos, one drive, yadisk, direct </small>
                  </div>
               </div>
               <div class="form-group mb-3 ">
                  <label class="form-label">Alternative  Link/s</label>
                  <div id="alt-links">
                     <?php 
                        foreach($link['altLinks'] as $ak => $alt): ?>
                     <div class="row row-sm mb-2" id="<?=$ak==0?'add-alt-link-dumy':''?>" >
                        <div class="col">
                           <div class="input-group mb-2">
                              <?php if(!empty($alt['link'])): ?>
                              <button class="btn  btn-secondary ibtn"  type="button"  data-toggle="tooltip" data-placement="top" title="<?=$alt['type']?>">
                              <img src="<?=Helper::getIcon($alt['type'])?>" height="15" alt="">
                              </button>
                              <span class="input-group-text">
                              <?=Helper::getStatus($alt['status'])?> 
                              </span>
                              <?php endif; ?>
                              <input type="text" class="form-control" name="alt[<?=$ak+1?>][link]" value="<?=$alt['link']?>" placeholder="Enter your alternative link">
                           </div>
                           <input type="text" name="alt[<?=$ak+1?>][is_remove]" class="is_remove_alt_link" hidden value="0">
                           <input type="text" name="alt[<?=$ak+1?>][id]" class="alt_link_id" hidden value="<?=$alt['id']?>">
                        </div>
                        <div class="col-auto align-self-center">
                           <a href="javascript:void(0)" class="link-secondary add-alt-link" title="" data-toggle="tooltip" data-original-title="add new alt link" style="vertical-align: middle;">
                              <svg class="icon icon-md" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path fill-rule="evenodd" d="M10 5.5a.5.5 0 01.5.5v4a.5.5 0 01-.5.5H6a.5.5 0 010-1h3.5V6a.5.5 0 01.5-.5z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M9.5 10a.5.5 0 01.5-.5h4a.5.5 0 010 1h-3.5V14a.5.5 0 01-1 0v-4z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                           <a href="javascript:void(0)" class="link-danger  remove-alt-link <?php if(empty($alt['link'])) echo 'd-none'; ?> " title="" data-toggle="tooltip" data-original-title="remove">
                              <svg class="icon " width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M7.5 7.5A.5.5 0 018 8v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm2.5 0a.5.5 0 01.5.5v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm3 .5a.5.5 0 00-1 0v6a.5.5 0 001 0V8z"></path>
                                 <path fill-rule="evenodd" d="M16.5 5a1 1 0 01-1 1H15v9a2 2 0 01-2 2H7a2 2 0 01-2-2V6h-.5a1 1 0 01-1-1V4a1 1 0 011-1H8a1 1 0 011-1h2a1 1 0 011 1h3.5a1 1 0 011 1v1zM6.118 6L6 6.059V15a1 1 0 001 1h6a1 1 0 001-1V6.059L13.882 6H6.118zM4.5 5V4h11v1h-11z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                           <a href="javascript:void(0)" class="link-secondary ml-2 move" title="" data-toggle="tooltip" data-original-title="move">
                              <svg class="icon icon-sm" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path fill-rule="evenodd" d="M4 11.5a.5.5 0 01.5.5v3.5H8a.5.5 0 010 1H4a.5.5 0 01-.5-.5v-4a.5.5 0 01.5-.5z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M8.854 11.11a.5.5 0 010 .708l-4.5 4.5a.5.5 0 11-.708-.707l4.5-4.5a.5.5 0 01.708 0zm7.464-7.464a.5.5 0 010 .708l-4.5 4.5a.5.5 0 11-.707-.708l4.5-4.5a.5.5 0 01.707 0z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M11.5 4a.5.5 0 01.5-.5h4a.5.5 0 01.5.5v4a.5.5 0 01-1 0V4.5H12a.5.5 0 01-.5-.5zm4.5 7.5a.5.5 0 00-.5.5v3.5H12a.5.5 0 000 1h4a.5.5 0 00.5-.5v-4a.5.5 0 00-.5-.5z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M11.146 11.11a.5.5 0 000 .708l4.5 4.5a.5.5 0 00.708-.707l-4.5-4.5a.5.5 0 00-.708 0zM3.682 3.646a.5.5 0 000 .708l4.5 4.5a.5.5 0 10.707-.708l-4.5-4.5a.5.5 0 00-.707 0z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M8.5 4a.5.5 0 00-.5-.5H4a.5.5 0 00-.5.5v4a.5.5 0 001 0V4.5H8a.5.5 0 00.5-.5z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                        </div>
                     </div>
                     <?php endforeach; ?>
                  </div>
                  <small class="form-hint">Supported sources: google drive, google photos, one drive, yadisk, direct </small>
               </div>
               <div class="form-group mb-3 ">
                  <label class="form-label">Subtitles</label>
                  <div class="" id="sub-list">
                     <?php foreach($link['subtitles'] as $k => $sub): ?>
                     <div class="row row-sm mb-2" id="<?=$k==0?'add-sub-dumy':''?>">
                        <div class="col-auto">
                           <select class="form-select" name="sub[<?=$k+1?>][label]" style="min-width: 175px;">
                              <?php    
                                 $subLables = json_decode($this->config['sublist'], true);
                                 foreach($subLables as $sublbl):
                                   $selected = $sub['label'] == $sublbl ? 'selected' : '';
                                 ?>
                              <option value="<?=$sublbl?>" <?=$selected?>><?=ucwords($sublbl)?></option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                        <div class="col">
                           <input type="file" name="sub[<?=$k+1?>][file]"  placeholder="Search for…">
                           <input type="text" name="sub[<?=$k+1?>][file]" class="sub-file" hidden value="<?=$sub['file']?>">
                           <input type="text" name="sub[<?=$k+1?>][is_remove]" class="is_remove_sub" hidden value="0">
                        </div>
                        <div class="col-auto align-self-center">
                           <a href="javascript:void(0)" class="link-secondary add-sub" title="" data-toggle="tooltip" data-original-title="add new" style="vertical-align: middle;">
                              <svg class="icon icon-md" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path fill-rule="evenodd" d="M10 5.5a.5.5 0 01.5.5v4a.5.5 0 01-.5.5H6a.5.5 0 010-1h3.5V6a.5.5 0 01.5-.5z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M9.5 10a.5.5 0 01.5-.5h4a.5.5 0 010 1h-3.5V14a.5.5 0 01-1 0v-4z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                           <?php if(!empty($sub['file'])): ?>
                           <a href="<?=Helper::getSubD($sub['file'])?>"  class="link-secondary download" title="" data-toggle="tooltip" data-original-title="download" style="vertical-align: middle;    ">
                              <svg class="icon" width="1em" height="1em" viewBox="0 0 20 20" style="font-size: 1.2rem;" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path fill-rule="evenodd" d="M6.646 11.646a.5.5 0 01.708 0L10 14.293l2.646-2.647a.5.5 0 01.708.708l-3 3a.5.5 0 01-.708 0l-3-3a.5.5 0 010-.708z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M10 4.5a.5.5 0 01.5.5v9a.5.5 0 01-1 0V5a.5.5 0 01.5-.5z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                           <a href="javascript:void(0)" class="link-danger remove-sub" title="" data-toggle="tooltip" data-original-title="remove">
                              <svg class="icon " width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M7.5 7.5A.5.5 0 018 8v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm2.5 0a.5.5 0 01.5.5v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm3 .5a.5.5 0 00-1 0v6a.5.5 0 001 0V8z"></path>
                                 <path fill-rule="evenodd" d="M16.5 5a1 1 0 01-1 1H15v9a2 2 0 01-2 2H7a2 2 0 01-2-2V6h-.5a1 1 0 01-1-1V4a1 1 0 011-1H8a1 1 0 011-1h2a1 1 0 011 1h3.5a1 1 0 011 1v1zM6.118 6L6 6.059V15a1 1 0 001 1h6a1 1 0 001-1V6.059L13.882 6H6.118zM4.5 5V4h11v1h-11z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                           <?php endif; ?>
                           <?php if(empty($sub['file']) && $k ==0): ?>
                           <a href="javascript:void(0)" class="link-secondary remove-sub d-none" title="" data-toggle="tooltip" data-original-title="remove">
                              <svg class="icon " width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M7.5 7.5A.5.5 0 018 8v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm2.5 0a.5.5 0 01.5.5v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm3 .5a.5.5 0 00-1 0v6a.5.5 0 001 0V8z"></path>
                                 <path fill-rule="evenodd" d="M16.5 5a1 1 0 01-1 1H15v9a2 2 0 01-2 2H7a2 2 0 01-2-2V6h-.5a1 1 0 01-1-1V4a1 1 0 011-1H8a1 1 0 011-1h2a1 1 0 011 1h3.5a1 1 0 011 1v1zM6.118 6L6 6.059V15a1 1 0 001 1h6a1 1 0 001-1V6.059L13.882 6H6.118zM4.5 5V4h11v1h-11z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                           <?php endif; ?>
                           <a href="javascript:void(0)" class="link-secondary ml-2 move" title="" data-toggle="tooltip" data-original-title="move">
                              <svg class="icon icon-sm" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <path fill-rule="evenodd" d="M4 11.5a.5.5 0 01.5.5v3.5H8a.5.5 0 010 1H4a.5.5 0 01-.5-.5v-4a.5.5 0 01.5-.5z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M8.854 11.11a.5.5 0 010 .708l-4.5 4.5a.5.5 0 11-.708-.707l4.5-4.5a.5.5 0 01.708 0zm7.464-7.464a.5.5 0 010 .708l-4.5 4.5a.5.5 0 11-.707-.708l4.5-4.5a.5.5 0 01.707 0z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M11.5 4a.5.5 0 01.5-.5h4a.5.5 0 01.5.5v4a.5.5 0 01-1 0V4.5H12a.5.5 0 01-.5-.5zm4.5 7.5a.5.5 0 00-.5.5v3.5H12a.5.5 0 000 1h4a.5.5 0 00.5-.5v-4a.5.5 0 00-.5-.5z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M11.146 11.11a.5.5 0 000 .708l4.5 4.5a.5.5 0 00.708-.707l-4.5-4.5a.5.5 0 00-.708 0zM3.682 3.646a.5.5 0 000 .708l4.5 4.5a.5.5 0 10.707-.708l-4.5-4.5a.5.5 0 00-.707 0z" clip-rule="evenodd"></path>
                                 <path fill-rule="evenodd" d="M8.5 4a.5.5 0 00-.5-.5H4a.5.5 0 00-.5.5v4a.5.5 0 001 0V4.5H8a.5.5 0 00.5-.5z" clip-rule="evenodd"></path>
                              </svg>
                           </a>
                        </div>
                        <?php if(!empty($sub['file'])): ?>
                        <div class="col-12 sub-label">
                           <span class="badge bg-blue-lt"><?=substr($sub['file'], 0, 100)?></span>
                        </div>
                        <?php endif; ?>
                     </div>
                     <!-- ./ row -->
                     <?php endforeach; ?>
                  </div>
                  <small class="form-hint">Supported formats : .srt, .vtt, .dfxp, .ttml, .xml</small>
               </div>
            </div>
         </div>
         <div class="card">
            <div class="card-header" style="    justify-content: space-between;">
               <h3 class="card-title">My HLS Link</h3>
               <button type= "button" class="btn btn-outline-primary" data-toggle="modal" data-target="#convert-to-hls-modal">Convert to HLS</button>
            </div>
            <div class="card-body">
               <?php if(!empty($hlsLinksLists)): ?>
               <?php foreach($hlsLinksLists as $bk => $blink): ?>
               <div class="row row-sm mb-2" >
                  <div class="col">
                     <input type="text" name="hls[<?=$bk?>][id]" value="<?=$blink['id']?>" hidden >
                     <!-- <label class="form-label">Disabled</label> -->
                     <div class="input-group">
                        <span class="input-group-text">
                           <?php $hlsSt = $blink['status'] == 0 ? 'success' : 'danger'; ?>
                           <a href="javascript:void(0)" class="text-<?=$hlsSt?> hls-sty" data-toggle="tooltip" data-placement="right" title="" data-original-title="Active">
                              <svg class="icon icon-sm" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <circle cx="10" cy="10" r="8"></circle>
                              </svg>
                           </a>
                        </span>
                        <input type="text" class="form-control backup-drive-file" name=""  placeholder="" value="<?=$blink['file']?>" readonly>
                     </div>
                     <small class="form-hint mt-1">File size : <b><?=Helper::formatSize($blink['file_size'])?></b> </small>
                     <input type="text" name="hls[<?=$bk?>][is_removed]" class="is_remove_hls_link" hidden="" value="0">
                  </div>
                  <div class="col-auto align-self-center mb-4">
                     <a href="javascript:void(0)" class="text-dark mr-2 refresh-hls-link spin" style="font-size: 1.2rem;" data-server-id="<?=$blink['server_id']?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Refresh">
                        <svg class="icon " width="1em"  height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                           <path fill-rule="evenodd" d="M4 9.5a.5.5 0 00-.5.5 6.5 6.5 0 0012.13 3.25.5.5 0 00-.866-.5A5.5 5.5 0 014.5 10a.5.5 0 00-.5-.5z" clip-rule="evenodd"></path>
                           <path fill-rule="evenodd" d="M4.354 9.146a.5.5 0 00-.708 0l-2 2a.5.5 0 00.708.708L4 10.207l1.646 1.647a.5.5 0 00.708-.708l-2-2zM15.947 10.5a.5.5 0 00.5-.5 6.5 6.5 0 00-12.13-3.25.5.5 0 10.866.5A5.5 5.5 0 0115.448 10a.5.5 0 00.5.5z" clip-rule="evenodd"></path>
                           <path fill-rule="evenodd" d="M18.354 8.146a.5.5 0 00-.708 0L16 9.793l-1.646-1.647a.5.5 0 00-.708.708l2 2a.5.5 0 00.708 0l2-2a.5.5 0 000-.708z" clip-rule="evenodd"></path>
                        </svg>
                     </a>
                     <a href="javascript:void(0)" class="link-danger remove-hls-link" style="font-size: 1rem;" data-id="<?=$blink['id']?>" title="" data-toggle="tooltip" data-original-title="remove">
                        <svg class="icon " width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                           <path d="M7.5 7.5A.5.5 0 018 8v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm2.5 0a.5.5 0 01.5.5v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm3 .5a.5.5 0 00-1 0v6a.5.5 0 001 0V8z"></path>
                           <path fill-rule="evenodd" d="M16.5 5a1 1 0 01-1 1H15v9a2 2 0 01-2 2H7a2 2 0 01-2-2V6h-.5a1 1 0 01-1-1V4a1 1 0 011-1H8a1 1 0 011-1h2a1 1 0 011 1h3.5a1 1 0 011 1v1zM6.118 6L6 6.059V15a1 1 0 001 1h6a1 1 0 001-1V6.059L13.882 6H6.118zM4.5 5V4h11v1h-11z" clip-rule="evenodd"></path>
                        </svg>
                     </a>
                  </div>
               </div>
               <?php endforeach; ?>    
               <?php else: ?>
               Not found hls links
               <?php endif; ?>
            </div>
         </div>
         <div class="card">
            <div class="card-header" style="    justify-content: space-between;">
               <h3 class="card-title">Google Drive Backup Links</h3>
               <?php
                  if($link['type'] != 'gdrive'  ) :
                     $disabled = !empty($this->getDriveAccounts(true, true)) ? 'disabled=""' : '';
                  ?>
               <a href="javascript:void(0)"   data-toggle="modal" data-target="#select-backup-acc-modal" class="btn btn-outline-primary <?=$disabled?>" >Backup</a>
               <?php endif; ?>
            </div>
            <div class="card-body">
               <?php if(!empty($backupDriveLinks)): ?>
               <?php foreach($backupDriveLinks as $bk => $blink): ?>
               <div class="row row-sm mb-2" id="add-alt-link-dumy">
                  <div class="col">
                     <input type="text" name="backup_drives[<?=$bk?>][id]" value="<?=$blink['id']?>" hidden >
                     <!-- <label class="form-label">Disabled</label> -->
                     <div class="input-group mb-2">
                        <span class="input-group-text">
                           <?php $hlsSt = $blink['status'] == 0 ? 'success' : 'danger'; ?>
                           <a href="javascript:void(0)" class="text-<?=$hlsSt?>" data-toggle="tooltip" data-placement="right" title="" data-original-title="Active">
                              <svg class="icon icon-sm" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                 <circle cx="10" cy="10" r="8"></circle>
                              </svg>
                           </a>
                        </span>
                        <input type="text" class="form-control backup-drive-file" name="backup_drives[<?=$bk?>][file]"  placeholder="" value="https://drive.google.com/file/d/<?=$blink['file_id']?>" readonly>
                     </div>
                     <?php $dAcc = isset($driveAccounts[$blink['acc_id']]['email']) ? $driveAccounts[$blink['acc_id']]['email'] : 'Not found !';  ?>
                     <small class="form-hint">Drive account : <b><?=$dAcc?></b> </small>
                     <input type="text" name="backup_drives[<?=$bk?>][is_removed]" class="is_remove_backup_link" hidden="" value="0">
                  </div>
                  <div class="col-auto align-self-center mb-4">
                     <a href="javascript:void(0)" style="font-size: 1.1rem;" class="link-secondary edit-backup-link  mr-2" title="" data-toggle="tooltip" data-original-title="edit">
                        <svg class="icon "   width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                           <path fill-rule="evenodd" d="M13.293 3.293a1 1 0 011.414 0l2 2a1 1 0 010 1.414l-9 9a1 1 0 01-.39.242l-3 1a1 1 0 01-1.266-1.265l1-3a1 1 0 01.242-.391l9-9zM14 4l2 2-9 9-3 1 1-3 9-9z" clip-rule="evenodd"></path>
                           <path fill-rule="evenodd" d="M14.146 8.354l-2.5-2.5.708-.708 2.5 2.5-.708.708zM5 12v.5a.5.5 0 00.5.5H6v.5a.5.5 0 00.5.5H7v.5a.5.5 0 00.5.5H8v-1.5a.5.5 0 00-.5-.5H7v-.5a.5.5 0 00-.5-.5H5z" clip-rule="evenodd"></path>
                        </svg>
                     </a>
                     <a href="javascript:void(0)" style="font-size: 1rem;" class="link-danger remove-backup-link" title="" data-toggle="tooltip" data-original-title="remove">
                        <svg class="icon " width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                           <path d="M7.5 7.5A.5.5 0 018 8v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm2.5 0a.5.5 0 01.5.5v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm3 .5a.5.5 0 00-1 0v6a.5.5 0 001 0V8z"></path>
                           <path fill-rule="evenodd" d="M16.5 5a1 1 0 01-1 1H15v9a2 2 0 01-2 2H7a2 2 0 01-2-2V6h-.5a1 1 0 01-1-1V4a1 1 0 011-1H8a1 1 0 011-1h2a1 1 0 011 1h3.5a1 1 0 011 1v1zM6.118 6L6 6.059V15a1 1 0 001 1h6a1 1 0 001-1V6.059L13.882 6H6.118zM4.5 5V4h11v1h-11z" clip-rule="evenodd"></path>
                        </svg>
                     </a>
                  </div>
               </div>
               <?php endforeach; ?>
               <?php else: ?>
               Not found backup links
               <?php endif; ?>
            </div>
         </div>
      </div>
      <div class="col-md-4">
         <div class="card">
            <div class="card-body">
               <div class="form-group mb-3 ">
                  <label class="form-label">Preview image</label>
                  <div>
                     <input type="file" name="preview_image" >
                     <?php if(!empty($link['preview_img'])): ?>
                     <div class="preview-img-wrap mt-2">
                        <input type="text" name="preview_image" value="<?=$link['preview_img']?>" hidden>
                        <img src="<?=Helper::getBanner($link['preview_img'])?>" class="w-100" alt="preview_image">
                        <a href="javascript:void(0)" class="text-danger remove-preview-img">remove</a>
                     </div>
                     <?php endif; ?>
                  </div>
               </div>
               <div class="form-group mb-3 ">
                  <label class="form-label">Custom slug</label>
                  <div>
                     <input type="text" class="form-control" name="slug" value="<?=$link['slug']?>" placeholder="Enter custom video slug" required>
                  </div>
               </div>
               <div class="form-group mb-3 ">
                  <label class="form-label">Select Account</label>
                  <select class="form-select" name="accountId" id="driveAccounts">
                     <option value=""></option>
                     <?php foreach($driveAccounts as $ak => $ac):
                        if($ac['status'] == 0) continue;
                        ?>
                     <option value="<?=$ak?>" <?php if($ak == $link['acc_id']) echo 'selected="selected"'; ?> ><?=$ac['email']?></option>
                     <?php endforeach; ?>
                  </select>
                  <!-- <label class="form-check">
                     <input class="form-check-input" id="select-account" type="checkbox">
                     <span class="form-check-label">Select it manually. &nbsp;
                     <a href="#"> <small>Learn more</small> </a>
                     </span>
                     </label> -->
               </div>
               <div class="form-group mb-3 ">
                  <label class="form-label">Link Status</label>
                  <select class="form-select" name="status">
                     <option value="active" <?=$link['status'] == 0 ? 'selected' : ''?> >Active</option>
                     <option value="inactive" <?=$link['status'] == 1 ? 'selected' : ''?> >Draft</option>
                  </select>
               </div>
               <div class="mb-3">
                  <ul>
                     <li> <b>Created At</b> : <i><?=Helper::formatDT($link['created_at'])?></i> </li>
                     <li> <b>Last Updated At</b> : <i><?=Helper::formatDT($link['updated_at'])?></i> </li>
                  </ul>
               </div>
               <div class="form-footer">
                  <?php if(!empty($nextLink)): ?>
                  <a href="<?=PROOT?>/links/edit/<?=$nextLink['id']?>" class="btn btn-block btn-info ">Go to next link</a>
                  <?php endif; ?>
                  <div class="hr-text">*****</div>
                  <a href="<?=PROOT?>/links/del/<?=$link['id']?>" class="btn btn-block btn-danger">Remove</a>
                  <button type="submit" class="btn btn-block btn-primary">Save link</button>
               </div>
            </div>
         </div>
      </div>
   </div>
</form>
<div class="row">
   <div class="col-md-4">
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">Direct Stream Link</h3>
         </div>
         <div class="card-body">
            <?php if($link['type'] == 'GDrive'): ?>
            <div class="position-relative">
               <textarea class="form-control"  readonly id="streamLink" rows='3'><?=Helper::getStreamLink($link['slug'])?></textarea>
               <button type="button" class="btn btn-sm btn-success position-absolute" id="copyStreamLink" style="bottom: 8px;right:8px;">copy</button>
            </div>
            <small>Available Qulities: <?=implode(', ',Helper::getQulities($link['data']))?></small>
            <?php else: ?>
            <small>Not Available !</small>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <div class="col-md-4">
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">Player Link</h3>
         </div>
         <div class="card-body">
            <div class="position-relative">
               <textarea class="form-control"  readonly id="plyrLink" rows='4'><?=Helper::getPlyrLink($link['slug'])?></textarea>
               <button type="button" class="btn btn-sm btn-success position-absolute" id="copyPlyrLink" style="bottom: 8px;right:8px;">copy</button>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-4">
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">Embed Code</h3>
         </div>
         <div class="card-body">
            <div class="position-relative">
               <textarea class="form-control" id="embedCode" readonly rows='4'><?=Helper::getEmbedCode(Helper::getPlyrLink($link['slug']))?></textarea>
               <button type="button" class="btn btn-sm btn-success position-absolute" id="copyEmbedCode" style="bottom: 8px;right:8px;">copy</button>
            </div>
         </div>
      </div>
   </div>
</div>
<div id="linkId" class="d-none"><?=$link['id']?></div>
<div class="modal modal-blur fade" id="select-backup-acc-modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <div class="mb-3">
               <label class="form-label">Select backup account</label>
               <?php 
                  $backupDrives = FH::getDriveAccounts(true, true);
                  if(!empty($backupDrives)): ?>
               <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                  <?php foreach($backupDrives as $bdk => $bdv  ): ?>
                  <label class="form-selectgroup-item flex-fill">
                     <input type="checkbox" name="selected-backup-drives[]" value="<?=$bdk?>" class="form-selectgroup-input" >
                     <div class="form-selectgroup-label d-flex align-items-center p-2">
                        <div class="mr-3">
                           <span class="form-selectgroup-check"></span>
                        </div>
                        <div class="form-selectgroup-label-content d-flex align-items-center">
                           <div class="strong"><?=$bdv['email']?></div>
                        </div>
                     </div>
                  </label>
                  <?php endforeach; ?>
               </div>
               <?php else: ?>
               <div class="alert alert-danger">Backup drives not found !</div>
               <div class="text-center">
                  <a href="<?=PROOT?>/settings/backup" class="link-primary">Create new backup drive</a>
               </div>
               <?php endif; ?>
            </div>
         </div>
         <?php if(!empty($backupDrives)): ?>
         <div class="modal-footer">
            <button type="button" class="btn btn-link link-secondary mr-auto" data-dismiss="modal">Cancel</button>
            <a href="javascript:void(0)"  class="btn btn-primary" id="create-backup" >Create backup</a>
         </div>
         <?php endif; ?>
      </div>
   </div>
</div>
<div class="modal modal-blur fade" id="convert-to-hls-modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <div class="mb-2">
               <?php 
                  if(!empty($hlsServers)): ?>
               <div class="d-none" id="hls-converting-status">
                  <label class="form-label "><span class="hls-st">Please wait... </span>
                  <span class="float-right"><span class="hlsbkp">0</span>%</span>     
                  </label>
                  <div class="progress progress-sm">
                     <div class="progress-bar" style="width: 0%" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only"> <span class="hlsbkp">0</span>% Complete</span>
                     </div>
                  </div>
               </div>
               <div class="servers-selection">
                  <label class="form-label">Select server</label>
                  <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                     <?php 
                        $i = 0;
                        foreach($hlsServers as $hlsK => $hlsV  ): ?>
                     <label class="form-selectgroup-item flex-fill">
                        <input type="radio" name="selected-hls-server" <?php if($i == 0) echo "checked='checked'"; ?>  data-url="<?=$hlsV['domain']?>" value="<?=$hlsV['id']?>" class="form-selectgroup-input" >
                        <div class="form-selectgroup-label d-flex align-items-center p-2">
                           <div class="mr-3">
                              <span class="form-selectgroup-check"></span>
                           </div>
                           <div class="form-selectgroup-label-content d-flex align-items-center">
                              <div class="strong"><?=$hlsV['name']?></div>
                           </div>
                        </div>
                     </label>
                     <?php $i++;endforeach; ?>
                  </div>
               </div>
               <?php else: ?>
               <div class="alert alert-danger">HLS servers not found !</div>
               <div class="text-center">
                  <a href="<?=PROOT?>/servers" class="link-primary">Add HLS server</a>
               </div>
               <?php endif; ?>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-link link-secondary mr-auto" data-dismiss="modal">Cancel</button>
            <a href="javascript:void(0)"  class="btn btn-primary" id="convert-hls" >Convert</a>
         </div>
      </div>
   </div>
</div>
<div class="modal modal-blur fade" id="e-del-confirm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <form action="<?=PROOT?>/links/del/hls">
         <div class="modal-content">
            <div class="modal-body">
               <div class="modal-title">Are you sure?</div>
               <div>you want to delete this HLS file ?</div>
               <div class="bg-yellow px-1 mt-2">
                  <input type="text" id="et" name="t" value="" hidden>
                  <input type="text" id="" name="linkId" value="<?=$link['id']?>" hidden>
                  <label class="form-check">
                  <input class="form-check-input" name="pdel"  type="checkbox">
                  <span class="form-check-label">Also delete video file from server</span>
                  </label>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-link link-secondary mr-auto" data-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-danger dlo" >Yes, delete</button>
               <!-- <a href="javascript:void(0)" id="e-del-link" class="btn btn-danger dlo" >Yes, delete</a> -->
            </div>
         </div>
      </form>
   </div>
</div>