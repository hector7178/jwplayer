<!-- Page title -->
<div class="page-header">
   <div class="row align-items-center">
      <div class="col-auto">
         <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="<?=PROOT?>/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0)">Servers</a></li>
         </ol>
      </div>
   </div>
</div>
<!-- Content here -->
<div class="row">
   <div class="col-md-8">
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">Servers List</h3>
         </div>
         <div class="card-body">
            <div id="alert-wrap"></div>
            <?php if(!empty($removedServerIds)): ?>
            <?php foreach($removedServerIds as $bk => $bv): ?>
            <div class="alert alert-danger"> <b><?=$bv?></b> server has beed deleted. Do you need delete permanently , releated links with this server ?
               &nbsp;
               <a href="javascript:void(0)" data-url="<?=PROOT?>/servers/del-server-links/<?=$bk?>" class="btn btn-danger btn-sm del-all-backup-links">Yes, delete that all</a>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            <div class="table-responsive">
               <table class="table table-vcenter card-table">
                  <thead>
                     <tr>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>Type</th>
                        <th>Playbacks</th>
                        <th>status</th>
                        <th class="w-1"></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach($servers as $server):
                        if($server['is_deleted']) continue; ?>
                     <tr data-id="<?=$server['id']?>">
                        <td class="server-name"><?=$server['name']?></td>
                        <td class="text-muted server-domain"> <a href="#" class="text-reset"><?=$server['domain']?></a></td>
                        <td class="server-type">
                           <?php if($server['type'] == 'stream'): ?>
                           <span class="badge bg-yellow-lt">stream</span>
                           <?php elseif($server['type'] == 'hls'): ?>
                           <span class="badge bg-purple-lt">hls</span>
                           <?php else: ?>
                           <span class="badge bg-cyan-lt">download</span>
                           <?php endif; ?>
                        </td>
                        <td class="text-muted"> <?=$server['playbacks']?></td>
                        <td class="text-muted">
                           <?php if($server['is_broken'] == 0): ?>
                           <span class="badge bg-green-lt">Connected</span>
                           <?php else: ?>
                           <span class="badge bg-red-lt">Failed</span>
                           <?php endif; ?>
                        </td>
                        <td>
                           <div class="btn-list flex-nowrap">
                              <?php if($server['status'] == 0): ?>
                              <a href="<?=PROOT?>/servers/status/<?=$server['id']?>" class="text-success ml-2"  data-toggle="tooltip" data-placement="top" title="Pasue">
                                 <svg class="icon" width="1em" height="1em" viewBox="0 0 20 20"  style="font-size:1.3rem"  fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.5 5.5A1.5 1.5 0 019 7v6a1.5 1.5 0 01-3 0V7a1.5 1.5 0 011.5-1.5zm5 0A1.5 1.5 0 0114 7v6a1.5 1.5 0 01-3 0V7a1.5 1.5 0 011.5-1.5z"></path>
                                 </svg>
                              </a>
                              <?php else: ?>
                              <a href="<?=PROOT?>/servers/status/<?=$server['id']?>" class="text-danger ml-2"  data-toggle="tooltip" data-placement="top" title="Active">
                                 <svg class="icon icon-md" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.596 10.697l-6.363 3.692c-.54.313-1.233-.066-1.233-.697V6.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 010 1.393z"></path>
                                 </svg>
                              </a>
                              <?php endif; ?>
                              <a href="javascript:void(0)" class="text-secondary ml-2 edit-server"  data-toggle="tooltip" data-placement="top" title="Edit">
                                 <svg class="icon" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" style="font-size:1.3rem" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M13.293 3.293a1 1 0 011.414 0l2 2a1 1 0 010 1.414l-9 9a1 1 0 01-.39.242l-3 1a1 1 0 01-1.266-1.265l1-3a1 1 0 01.242-.391l9-9zM14 4l2 2-9 9-3 1 1-3 9-9z" clip-rule="evenodd"></path>
                                    <path fill-rule="evenodd" d="M14.146 8.354l-2.5-2.5.708-.708 2.5 2.5-.708.708zM5 12v.5a.5.5 0 00.5.5H6v.5a.5.5 0 00.5.5H7v.5a.5.5 0 00.5.5H8v-1.5a.5.5 0 00-.5-.5H7v-.5a.5.5 0 00-.5-.5H5z" clip-rule="evenodd"></path>
                                 </svg>
                              </a>
                              <a href="javascript:void(0)" class="text-info ml-2 refresh-server"  data-toggle="tooltip" data-placement="top" title="Refresh">
                                 <svg class="icon " width="1em"  style="font-size:1.3rem" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 9.5a.5.5 0 00-.5.5 6.5 6.5 0 0012.13 3.25.5.5 0 00-.866-.5A5.5 5.5 0 014.5 10a.5.5 0 00-.5-.5z" clip-rule="evenodd"></path>
                                    <path fill-rule="evenodd" d="M4.354 9.146a.5.5 0 00-.708 0l-2 2a.5.5 0 00.708.708L4 10.207l1.646 1.647a.5.5 0 00.708-.708l-2-2zM15.947 10.5a.5.5 0 00.5-.5 6.5 6.5 0 00-12.13-3.25.5.5 0 10.866.5A5.5 5.5 0 0115.448 10a.5.5 0 00.5.5z" clip-rule="evenodd"></path>
                                    <path fill-rule="evenodd" d="M18.354 8.146a.5.5 0 00-.708 0L16 9.793l-1.646-1.647a.5.5 0 00-.708.708l2 2a.5.5 0 00.708 0l2-2a.5.5 0 000-.708z" clip-rule="evenodd"></path>
                                 </svg>
                              </a>
                              <a href="javascript:void(0)" class="text-danger del-server ml-2" data-url="<?=PROOT?>/servers/del/<?=$server['id']?>"   data-toggle="tooltip" data-placement="top" title="Delete">
                                 <svg class="icon " width="1em"  style="font-size:1.3rem" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.5 7.5A.5.5 0 018 8v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm2.5 0a.5.5 0 01.5.5v6a.5.5 0 01-1 0V8a.5.5 0 01.5-.5zm3 .5a.5.5 0 00-1 0v6a.5.5 0 001 0V8z"></path>
                                    <path fill-rule="evenodd" d="M16.5 5a1 1 0 01-1 1H15v9a2 2 0 01-2 2H7a2 2 0 01-2-2V6h-.5a1 1 0 01-1-1V4a1 1 0 011-1H8a1 1 0 011-1h2a1 1 0 011 1h3.5a1 1 0 011 1v1zM6.118 6L6 6.059V15a1 1 0 001 1h6a1 1 0 001-1V6.059L13.882 6H6.118zM4.5 5V4h11v1h-11z" clip-rule="evenodd"></path>
                                 </svg>
                              </a>
                           </div>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <?php if(!empty($hlsStData)): ?>
      <div class="card mt-5">
         <div class="card-header">
            <h3 class="card-title">HLS servers storage </h3>
         </div>
      </div>
      <div class="row row-deck row-cards">
         <?php foreach($hlsStData as $hlsK => $hlsV): 
            if(empty($hlsV['data']['meta'])) continue;
            ?>
         <div class="col-md-6">
            <div class="card">
               <div class="card-body">
                  <h3 class="card-title" ><?=$hlsV['server']?>    <small class="float-right"> <span class="bg-indigo-lt px-2"><?=$hlsV['tLinks']?></span> <span style="font-weight: 400;"> total links</span> </small>
                  </h3>
                  <div id="hls-server-<?=$hlsK?>" >
                  </div>
                  <div class="text-center mt-2"> <b class="bg-blue-lt"><?=$hlsV['data']['total']?></b> Total  <b class=" ml-3 bg-red-lt"><?=$hlsV['data']['used']?></b> Used  </div>
                  <small class="text-center form-hint mt-1"><?=$hlsV['data']['dir']?></small>
               </div>
            </div>
         </div>
         <?php endforeach; ?>
      </div>
      <?php endif; ?>
   </div>
   <div class="col-md-4">
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">Add/Edit server</h3>
         </div>
         <div class="card-body">
            <?=$this->displayAlerts()?>
            <form action="<?=$_SERVER['REQUEST_URI']?>" method="post" id="form-server">
               <input type="text" id="server-id" class="form-control" name="id" hidden placeholder="">
               <div class="form-group mb-3">
                  <label class="form-label">Name</label>
                  <input type="text" id="server-name" class="form-control"  name="name" placeholder="cdn1">
               </div>
               <div class="form-group mb-3">
                  <label class="form-label">Type</label>
                  <select class="form-select" name="type" id="server-type">
                     <option value="stream">Stream</option>
                     <option value="hls">HLS</option>
                  </select>
               </div>
               <div class="form-group mb-3">
                  <label class="form-label">Doamin</label>
                  <input type="url" id="server-domain" class="form-control" name="domain" placeholder="http://cdn1.mydomain.com" required>
               </div>
               <div class="form-footer">
                  <button type="reset" class="btn btn-secondary  btn-block">Reset</button>
                  <button type="submit" class="btn btn-primary btn-block">Save</button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>