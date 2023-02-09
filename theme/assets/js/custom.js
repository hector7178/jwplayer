
  	$('#select-tdrives').selectize({
        render: {
            option: function (data, escape) {
                return '<div class="option">' + data.avatar + '' + escape(data.text) + '</div>';
            },
            item: function (data, escape) {
                return '<div class="d-flex align-items-center">' + data.avatar + '' + escape(data.text) + '</div>';
            }
        }
    });

// 

// var ids = '';
// $('.selected-for-delete').each(function(i, obj) {
//         ids += $(this).attr('data-id') + ',';
// });

// 

$("#filter-imported-links").on('click', function(){
    var s = $('input[name="fidf"]:checked');
    var uf =  window.location.origin + window.location.pathname;;
    if(s != undefined && s.length != 0){
       
        var u = uf + '?' + insertParam('filter',1);
        
    }else{
        var u = uf + '?' + insertParam('filter',0);
    }


window.location.href = u;

});


function insertParam(key, value) {
    key = encodeURIComponent(key);
    value = encodeURIComponent(value);

    // kvp looks like ['key1=value1', 'key2=value2', ...]
    var kvp = document.location.search.substr(1).split('&');
    let i=0;

    for(; i<kvp.length; i++){
        if (kvp[i].startsWith(key + '=')) {
            let pair = kvp[i].split('=');
            pair[1] = value;
            kvp[i] = pair.join('=');
            break;
        }
    }

    if(i >= kvp.length){
        kvp[kvp.length] = [key,value].join('=');
    }

    // can return this or...
    let params = kvp.join('&');

    // reload page with new params
    return params;
}






$(".import-drive-file, .import-selected-drive-files").on('click', function(){

    var dt = $(this).attr('data-id');
    var links = [];

    if(dt != undefined){

        var selecetdDriveFiles = $('input[name="selected-drive-files[]"]:checked');
        if(selecetdDriveFiles != undefined && selecetdDriveFiles.length != 0){
            $('input[name="selected-drive-files[]"]:checked').each(function() {
                var link = 'https://drive.google.com/file/d/'+this.value+'/view?usp=sharing';
                links.push(link);
             });
        }else{
            alert('select files');
        }

    }else{
        var fileId = $(this).closest('tr').attr('data-id');
        var link = 'https://drive.google.com/file/d/'+fileId+'/view?usp=sharing';
        links.push(link);
    }

    
    if(links.length != 0){

        var totalLinks =  $('.t-links').attr('data-lt');
        if(totalLinks == undefined || totalLinks == ''){
            totalLinks = 0;
        }
        totalLinks = parseInt(totalLinks) + 1;
        $('.p-links').text(get_b_pen_links()+1);
        $('.t-links').text(get_b_tot_links()+1);
        console.log(links);
        importing();
        addLinks(links);
    
        $(this).addClass('disabled');
    
        $('.df').removeClass('d-none');

    }
    


    




});




$("#select_all_drive_files, .select-d-f").change(function() {

    // var movieId = $(this).attr('data-movie-id');

    if($(this).prop('checked')) {
        if($(this).hasClass('sel-f-g')){
            $(this).prop('checked', true);
        }else{
            $('.select-d-f').prop('checked', true);
        }
            
            var selecetdDriveFiles = $('input[name="selected-drive-files[]"]:checked');
            if(selecetdDriveFiles != undefined && selecetdDriveFiles.length != 0){
                $('.import-selected-drive-files').removeClass('d-none');
            }else{
                alert('Files not found !');
            }
    } else {
        if($(this).hasClass('sel-f-g')){
            $(this).prop('checked', false);
        }else{
            $('.select-d-f').prop('checked', false);
        }
            
            if(!$('.import-selected-drive-files').hasClass('d-none')){
                $('.import-selected-drive-files').addClass('d-none');
            }
            
    }

   



   
});






// mcd-file

$(".mcd-file").on('click', function(){

    var selectdFileId = $(this).parent().parent().parent().attr('data-id');

    if(!$("#mcd-file-modal").hasClass('processing')){
        $("#make-a-drive-file-copy").removeAttr('disabled');

        if(!$("#mcd-file-modal .resp-wrap").hasClass('d-none')){
            $("#mcd-file-modal .resp-wrap").addClass('d-none');
        }
        if($("#mcd-file-modal .df-wrap").hasClass('d-none')){
            $("#mcd-file-modal .df-wrap").removeClass('d-none');
        }
        
        if($("#mcd-file-modal .resp-wrap .rpt").hasClass('d-none')){
            $("#mcd-file-modal .resp-wrap .rpt").removeClass('d-none');
        }

      
    }

    
    $("#selectdFileId").text(selectdFileId);
    $("#mcd-file-modal").modal('show');

})

$("#make-a-drive-file-copy").on('click', function(){

    

    var selectedAccount = $("#selectedDriveAcc").val();
    var activeDriveId = $('#activeDriveId').attr('data-id');
    var selectedDriveFile = $("#selectdFileId").text();

    var $this = $(this);
    $this.text('please wait...');
    $this.attr('disabled','disabled');

    if(selectedAccount != undefined && selectedAccount != ''){
        $("#mcd-file-modal").addClass('processing');
        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'fileId='+selectedDriveFile+'&activeDriveId='+activeDriveId+'&selectedDriveId='+selectedAccount+'&type=make-drive-copy',
            cache: false,
            success: function (data) {
                console.log(data);
                if(data.success){

                    $("#copied-file-id").attr('value',data.fileId);
                    var alrt = '<div class="alert alert-success">File copied successfully!</div>';
                    
                    $("#mcd-file-modal .resp-wrap .rpt").removeClass('d-none');
                }else{
                    if(data.error == undefined){
                        var e = 'Something went wrong !';
                    }else{
                        var e = data.error;
                    }
                    var alrt = '<div class="alert alert-danger">'+e+'</div>';
                }
                $("#mcd-file-modal .resp-wrap .alrt").html(alrt);
                $("#mcd-file-modal .resp-wrap").removeClass('d-none');
                $("#mcd-file-modal .df-wrap").addClass('d-none');
                $("#mcd-file-modal").removeClass('processing');
                $this.text('make a copy');
            },
            error: function (xhr) { // if error occured
                alert("Error occured.please try again");
                $("#mcd-file-modal").removeClass('processing');
                $this.text('make a copy');
                
            }
        });
    }

});



$(".refresh-hls-link").on('click', function(){

    var serverId = $(this).attr('data-server-id');
    var linkId = $('#linkId').text();
    var $this = $(this);
    var $pCobj = $this.parent().parent().find('.hls-sty');

    if(serverId != undefined && serverId != ''){
        if(!$this.find('svg').hasClass('spin'))
        {
            $this.find('svg').addClass('spin');
        }
        var $this = $(this);
        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'linkId='+linkId+'&sId='+serverId+'&type=get-hls-converter-status',
            cache: false,
            success: function (data) {
                // console.log(data);
                if(data.success){



                    data = data.data;

                    if(data.status ==  'processing'){
                        
                        alert('File converting process running....');

                    }
                    if(data.status == 'exist'){

                        alert('File is active !');
                        if(!$pCobj.hasClass('text-success')){
                            $pCobj.removeClass('text-danger');
                            $pCobj.addClass('text-success');
                        }
                    }

                    if(data.status == 'not exist'){
                        alert('File is not found !');
                        if($pCobj.hasClass('text-success')){
                            $pCobj.removeClass('text-success');
                            $pCobj.addClass('text-danger');
                        }
                    }

                    
                }
                $this.find('svg').removeClass('spin');
            },
            error: function (xhr) { // if error occured
                alert("Error occured.please try again");
                $this.find('svg').removeClass('spin');
            }
        });

    }


})


$("#convert-hls").on('click', function(){

    var selecetd = $('input[name=selected-hls-server]:checked').val();
    $(this).addClass('disabled');
    if(selecetd !== undefined && selecetd != ''){
        dontLeave();
        $('.servers-selection').addClass('d-none');
        $('#hls-converting-status').removeClass('d-none');
        runConverter(selecetd);
        
        setTimeout(function(){ 
            if(!$("#convert-to-hls-modal .modal-body").hasClass('alert')){
                updateStatus(selecetd);
            }
            

         }, 5000);



    }else{
        alert('Select server ');
    }

});



function runConverter(sid){
    var linkId = $("#linkId").text();
    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: 'linkId='+linkId+'&sId='+sid+'&type=convert-to-hls',
        cache: false,
        success: function (data) {
            if(!data.success){
                if(data.error != undefined && data.error != ''){
                    var alrt = "<div class='alert alert-danger'>"+data.error+"</div>";
                    $("#convert-to-hls-modal .modal-body").html(alrt);
                }
            }
            youCanLeave();
        },
        error: function (xhr) { // if error occured
            alert("Error occured.please try again");
            youCanLeave();
        }
    });

}


function updateStatus(sid){
    var linkId = $("#linkId").text();
    if(!$("#convert-to-hls-modal .modal-body").hasClass('alert')){
        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'linkId='+linkId+'&sId='+sid+'&type=get-hls-converter-status',
            cache: false,
            success: function (data) {
                // console.log(data);
                if(data.success){

                    data = data.data;

                    if(data.status ==  'processing'){
                        
                        if(data.data != undefined){
                            fdata = data.data;
                            if(fdata.source == 'gdrive'){
                                $(".hls-st").text('Downloading...');
                            }
                            if(fdata.source == 'ffmpeg'){
                                
                                $(".hls-st").text('Converting...');
                            }
                            $('.hlsbkp').text(fdata.progress);
                            $("#hls-converting-status .progress .progress-bar").attr('style', 'width:' + fdata.progress + '%');
                            $("#hls-converting-status .progress .progress-bar").attr('aria-valuenow', fdata.progress);
                            
                        }
                        setTimeout(function(){ 
                            updateStatus(sid);
                         }, 5000);

                    }
                    if(data.status == 'exist'){
                        var alrt = "<div class='alert alert-success'>File converted to HLS successfully!</div>";
                        $("#convert-to-hls-modal .modal-body").html(alrt);
                    }

                    if(data.status == 'failed'){
                        var alrt = "<div class='alert alert-danger'>Something went wrong!</div>";
                        $("#convert-to-hls-modal .modal-body").html(alrt);
                    }

                    if(data.status == 'not exist'){
                        var alrt = "<div class='alert alert-danger'>Something went wrong!</div>";
                        $("#convert-to-hls-modal .modal-body").html(alrt);
                    }
                 


                    
                    
    
                }else{
                    alert("Error occured. try again !");
                }

            },
            error: function (xhr) { // if error occured
                alert("Error occured.please try again");
            }
        });
    }


}





$("#create-backup-multi").on('click', function(){

    var selecetdDrives = $('input[name="selected-backup-drives[]"]:checked');

    if(selecetdDrives.length > 0){


        var selcetdDriveIds = '';
        $('input[name="selected-backup-drives[]"]:checked').each(function() {
            
            selcetdDriveIds += this.value + ',';

         });

         var selectedLinks = $('input[name="selected-links[]"]:checked');

         if(selectedLinks.length > 0){

            var selectedLinks = [];

            $('input[name="selected-links[]"]:checked').each(function() {
            
                selectedLinks.push(this.value);
               
                
             });
             

             $('.t-links').text(selectedLinks.length);

             $('.bk-form').addClass('d-none');
             $('#process-status').removeClass('d-none');
             $(this).addClass('disabled');
             
             
             backup_drive_file(selectedLinks, selcetdDriveIds);


         }else{
             alert('please select links !');
         }



     



    }

});

function updateBackupStatus(){

    
    var tlinks = parseInt($('.t-links').text());
    var clinks = parseInt($('.c-links').text());

    


    if(tlinks != clinks)
    {
    
      
        var st = Math.round(((clinks + 1) * 100) / tlinks);

        
        $(".bkp").text(st );
       


        $("#process-status .progress .progress-bar").attr('style', 'width:' + st + '%');
        $("#process-status .progress .progress-bar").attr('aria-valuenow', st);

        
        $('.c-links').text(clinks+1);

        if(st == 100){


            setTimeout(function() { 
                $("#select-backup-acc-modal").modal('hide');
                $("#process-status .progress .progress-bar").attr('style', 'width:0%');
                $("#process-status .progress .progress-bar").attr('aria-valuenow', 0);
    
                $("#create-backup-multi").removeClass('disabled');
                
                $('.c-links').text(0);
                $('.bk-form').removeClass('d-none');
                $('#process-status').addClass('d-none');
                scrollToTop();
            }, 1000);

        }



    }
   



   







}

function backup_drive_file(linksIds = [], selectedDrives = ''){



    if(typeof linksIds[0] !== 'undefined') {
        
        var linkId = linksIds[0];
        linksIds.splice(0,  1);
       
        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'id='+linkId+'&selected-drives='+selectedDrives+'&type=backup-drive-file',
            cache: false,
            success: function (data) {
                console.log(data.data);
                if(data.success)
                {   
var data = data.data;
    
    
                    if(data.errMsgs.length != 0){
                        var a = data.errMsgs;
                        while(a.length) { 
                            displayAlert(a.shift(), 'danger', true);
                        }
                    }
                    
    
                    // $this.removeClass('disabled');
                    // window.scrollTo(0, 0);
                    updateBackupStatus();
                    backup_drive_file(linksIds, selectedDrives);
                    
                }
                else
                {
                    alert("Error occured.please try again");
                }
                
                
            
            },
            error: function (xhr) { // if error occured
                alert("Error occured.please try again");
                updateBackupStatus();
            }
        });
    
    
    
    }




   




























































}
$(".remove-hls-link").on('click', function(){

    var hlsId = $(this).attr('data-id');
    $("#e-del-confirm #et").attr('value',hlsId);
    $("#e-del-confirm").modal('show');

    // $(this).parent().parent().addClass('d-none');
    // $(this).parent().parent().find('.is_remove_hls_link').val(1);

    // var link = $(this).attr('data-url');
    // $("#del-link").attr('href',link);
    // $("#del-confirm .ctxt").text('this server');
    // $("#del-confirm").modal('show');


});


$(".remove-backup-link").on('click', function(){

    $(this).parent().parent().addClass('d-none');
    $(this).parent().parent().find('.is_remove_backup_link').val(1);

});

$(".edit-backup-link").on('click', function(){

    $(this).parent().parent().find('.backup-drive-file').removeAttr('readonly');

});



$("#create-backup").on('click', function(){




    var selecetdDrives = $('input[name="selected-backup-drives[]"]:checked');

    if(selecetdDrives.length > 0){


        var $this = $(this);
        var $ct = $this.text();
        $this.addClass('disabled');
        $this.text('please wait...');




        var selcetdDriveIds = '';
        $('input[name="selected-backup-drives[]"]:checked').each(function() {
            
            selcetdDriveIds += this.value + ',';

         });

         var linkId = $('#linkId').text();

         $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'id='+linkId+'&selected-drives='+selcetdDriveIds+'&type=backup-drive-file',
            cache: false,
            success: function (data) {
                console.log(data.data);
                if(data.success)
                {   data = data.data;
    
                    if(data.exists.length != 0){
                        var a = data.exists;
                        while(a.length) { 
                            displayAlert('File is already backuped to <b> ' + a.shift() + '</b> drive account.', 'warning', true);
                        }
                    }
    
                    if(data.success.length != 0){
                        var a = data.success;
                        while(a.length) { 
                            displayAlert('File is backuped to <b> ' + a.shift() + '</b> drive account sucessfully.', 'success', true);
                        }
                    }
    
                    if(data.errors.length != 0){
                        var a = data.errors;
                        while(a.length) { 
                            displayAlert('File backup failed to <b> ' + a.shift() + '</b> drive account.', 'danger', true);
                        }
                    }
    
    
                    if(data.errMsgs.length != 0){
                        var a = data.errMsgs;
                        while(a.length) { 
                            displayAlert(a.shift(), 'danger', true);
                        }
                    }
    
    
    
                    // $this.removeClass('disabled');
                    scrollToTop();

                    
                }
                else
                {
                    alert("Error occured.please try again");
                }
                $("#select-backup-acc-modal").modal('hide');
                $this.text($ct);
            },
            error: function (xhr) { // if error occured
                alert("Error occured.please try again");
                $("#select-backup-acc-modal").modal('hide');
                $this.text($ct);
               
            }
        });

         
         

    }else{
        alert('please select backup account !');
    }

    

});




// console.log($('input[name="locationthemes"]:checked').serialize());





function scrollToTop(){
    window.scrollTo(0, 0);
}



































































$('.select-drive-account').on('change', function() {
    $("#activeDriveId").attr('data-id', this.value);
});

if($('#summernote').length !== 0){
    $('#summernote').summernote({
        placeholder: 'Type your page content',
        height: 300
    });
}










function displayDownloadLink(link, source){

    // <p>Your download link is ready.</p>
    // <a href="#" class="dlink">Click here to download</a>
    // <hr>

    var t = '<p>'+source+' download link is ready.</p>';
    var l = '<a href="'+link+'" target="_blank" class="dlink">Click here to download</a><hr>';

    var selmt = "."+source+""; 
    var melmt = ".mp-progress " + selmt;
     
    if($(".mp-progress").find(selmt).length !== 0){
        $(melmt).html(t+l);
    }


}

function noDownloadLink(error, source){

    var t = '<p class="text-danger">'+error+'</p>';
    var selmt = "."+source+""; 
    var melmt = ".mp-progress " + selmt;
     
    if($(".mp-progress").find(selmt).length !== 0){
        $(melmt).html(t);
    }

}



$(".get-download-link").on('click', function(){


   
     var $this = $(this);
     
     var source = $this.attr('data-id');
     $this.addClass('disabled');


     var ptext = '<p>Preparing download link. please wait...</p>';
     var pbar = '<div class="dp-progress-wrap"><div class="progress progress-sm">';
     pbar += '<div class="progress-bar progress-bar-indeterminate"></div></div>';
     pbar += '<span class="counter " style="display:none" id="'+source+'-counter">0%</span></div>';


    var selmt = "."+source+""; 
     
    if($(".mp-progress").find(selmt).length === 0){
        var html = '<div class="'+source+'">';
        html += ptext + pbar;
        html += '</div>';
        $(".mp-progress").append(html);
    }
       
         
    getDownloadLink(source);  
       

 });
 

function getDownloadLink(source, r = false){

    var linkId = getLinkId();

    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: 'linkId='+linkId+'&source='+source+'&type=get-download-link',
        cache: false,
        

        success: function (data) {
            
           console.log(data);

           if(data.success){

               displayDownloadLink(data.link, source);

           }else{
               if(data.reupload && !r){
                   reupload(data._key);
                   setTimeout(function () {
                       updateDLProcess(data._key, source);
                   }, 6000)
                   
               }else{
                    noDownloadLink(data.error, source);
               }
           }
            
            
        },
        error: function (xhr) { // if error occured
            console.log("Error occured.please try again.");
            
          
        }
    });

}







function reupload(key){
   $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: '_key='+key+'&type=prepapre-download-link',
        cache: false,
        // timeout: 5000,
        success: function (data) {},
        error: function (xhr) { }
    });
}




function updateDLProcess(key, source){
     
    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: '_k='+key+'&s=reuploading-status&type=status',
        cache: false,
        success: function (data) {
            
           console.log(data);
           
           if(data.success){
           
                var selmt = "#"+source+"-counter"; 

                if('progress' in data.data){
                    $(selmt).show();
                    $(selmt).text(data.data.progress);

                    console.log('Ajax');
                    setTimeout(function () {
                        updateDLProcess(key,source);
                    }, 5000)

                }else{
                    var e = '';
                    if('status' in data.data){
                        if(data.data.status == 'success'){
                            $(selmt).text('100%');
                            getDownloadLink(source, true);
                        }else{
                            if(data.data.error.length != 0){
                                console.log(data.data.error);
                            }
                            e = 'Unable to create ' + source + ' download link !';
                            noDownloadLink(e, source);
                        }
                    }else{
                        alert('unknown error !');
                    }
                }

           }else{
               if('__x' in data.data){
                   alert('Try again !');
                    window.location.reload();
               }
           }

        },
        error: function (xhr) { // if error occured
            console.log("Error occured.please try again.");
        }
    });

}


function downloadLinkIsReady(){

}


























if($("#mydrive").length != 0){

    var app = document.getElementById('console');


    var typewriter = new Typewriter(app, {
        loop: false,
        delay: 0,
      });
    
    
      $("#console .Typewriter__wrapper").append('Google Drive Uploader [Version 1.0]<br>2021 Developed by CodySeller. All rights reserved.');
    
      typewriter.typeString('<br><br>');
      typewriter.start();


      window.setInterval(function() {
        if($("#console").hasClass('drive-upload-processing')){
            var elem = document.getElementById('console');
            elem.scrollTop = elem.scrollHeight;
        }
      }, 500);



}














  $("#upload-to-drive").on('click', function(){
    

    var linkList = $("#link-list").val().split('\n');
    
    var links = [];

    for (var i=0; i < linkList.length; i++) {
        if (/\S/.test(linkList[i])) {
            links.push($.trim(linkList[i]));
        }
    }


    if(links.length > 0)
    {
        $("#link-list").val(' ');
        $(this).hide();
        $("#drive-upload-form").hide();
        $("#console").show();
        $("#console").addClass('drive-upload-processing');
        typewriter
        .typeString('Upload process started.<br>')
        .start();

        uploadToDrive(links);
        // updateDriveUploadingStatus




    }



});


function updateDriveUploadingStatus(c =1, d = ''){

    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: 'type=get-drive-upload-process',
        cache: false,
        success: function (data) {
            console.log(data);
            if(data.success)
            {
                data = data.data;
                if(data != ''){
                    if(data.status == 'success'){
                        //uploaded sucessfully


                        var elmt = ".file-uploading-" + c + " b";
                        var b = "[" + "#".repeat(100/2) + " 100% " + "]";

                    $("#console").find(elmt).text(b);

                        typewriter
                        .typeString('<br>Uploaded File ID : ' + data.fileId)
                        .typeString('<br>File is uploaded successfully !')
                        .start();

                        typewriter
                        .typeString('<br><span>...</span>')
                        .start();
                        uploadToDrive(d, c+1);


                    }else if(data.status == 'processing'){
                        //processing

                    if($("#console").find('span.file-'+c).length  !== 0){
                        if($("#console").find('span.file-uploading-'+c).length  === 0){
                            typewriter
                            .typeString('<span class="file-uploading-'+c+'"><br>Uploading... <b>0%<b> </span>')
                            .start();
                        }
                    }


                    var  progress = data.progress;


                    var elmt = ".file-uploading-" + c + " b";
                    
                
                    var dd = "[" + "#".repeat(progress/2) + "_".repeat((100-progress)/2) + " " + progress + "%  ]";
                   
                    $("#console").find(elmt).text(dd);
                    setTimeout(function(){
                        updateDriveUploadingStatus(c, d);
                    }, 3000);

                    }else if(data.status == 'failed'){
                        // alert('Unknonw uploading status !');
                         typewriter
                        .typeString('<br><span class="text-danger">Error occured. '+data.error+'</span>')
                        .typeString('<br>Upload failed !')
                        .start();
                    }else{
                        alert('Unknown status recived !');
                    }
                }else{
                    //waiting
                    setTimeout(function(){
                        updateDriveUploadingStatus(c, d);
                    }, 3000);
                }
            }
            else
            {
                alert('Uploading process not found !');
            }
          
        },
        error: function (xhr) { // if error occured
            alert("Error occured.please try again");        
            
        }
    });

}


function uploadToDrive(d, c = 1){

    var filename = '';

    if(typeof d[0] !== 'undefined') {
        
        var f= '';
        var driveId = getDriveId();
        var folderId = getFolderId();
        var vd = d[0].split(',');
        if(1 in vd){
            filename = vd[1];
        }
        var url = vd[0];
        d.splice(0,  1);
        var responseLen = 0;

        switch(c) {
            case 1:
              f = 'st';
              break;
            case 2:
                f = 'nd';
              break;
            case 3:
                f = 'rd';
            break;
            default:
                f = 'th';
          }

        typewriter
        .typeString('<span class="file-'+c+'"><br>Attempt to upload ' + c + f + ' file...</span>')
        .start();

        setTimeout(function(){
            updateDriveUploadingStatus(c, d);
        }, 5000);



        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'url='+url+'&driveId='+driveId+'&filename='+filename+'&folderId='+folderId+'&type=upload-to-drive',
            cache: false,
            success: function (data) {
                console.log(data);
                
                

            },
            error: function (xhr) { // if error occured
                alert("Error occured.please try again");
                typewriter
                .typeString('<br><span>...</span>')
                .start();
                uploadToDrive(d, c+1);
            }
        });

    }else{

        typewriter
        .typeString('<br><span>Upload process completed !</span>')
        .start();
        $("#console").removeClass('drive-upload-process-completed');
        $("#console").removeClass('drive-upload-processing');
    }



}

function dontLeave(){
    if(!$("#console2").hasClass('drive-upload-processing')){
        $("#console2").addClass('drive-upload-processing');
    }
}

function youCanLeave(){
    $("#console2").removeClass('drive-upload-processing');
}

window.onbeforeunload = function() {
    if($("#console, #console2").hasClass('drive-upload-processing')){
        return "Data will be lost if you leave the page, are you sure?";
    }
  };


  function driveUploadCompleted(){
    if($("#console").hasClass('drive-upload-process-completed')){
        return true;
    }
    return false;
  }


function isValidJson(json) {
    try {
        JSON.parse(json);
        return true;
    } catch (e) {
        return false;
    }
}





//function to check if element is scrolled to the bottom










function extractJSON(str) {
    var firstOpen, firstClose, candidate;
    firstOpen = str.indexOf('{', firstOpen + 1);
    do {
        firstClose = str.lastIndexOf('}');
        if(firstClose <= firstOpen) {
            return null;
        }
        do {
            candidate = str.substring(firstOpen, firstClose + 1);
            try {
                var res = JSON.parse(candidate);
                return [res, firstOpen, firstClose + 1];
            }
            catch(e) {
            }
            firstClose = str.substr(0, firstClose).lastIndexOf('}');
        } while(firstClose > firstOpen);
        firstOpen = str.indexOf('{', firstOpen + 1);
    } while(firstOpen != -1);
}


















$("#select-account").on('click', function(){
    if($(this).prop('checked') == true){
        $("#driveAccounts").removeAttr('disabled');
    }else{
        $("#driveAccounts").attr('disabled','disabled');
    }
});


$(".del-drive-file").on('click', function(){
    
    var fileId = $(this).closest('tr').attr('data-id');
    var driveId = getDriveId();
    $("#del-link").attr('href','javascript:void(0)');
    $("#del-link").attr('data-file-id',fileId);
    $("#del-link").attr('data-drive-id',driveId);
    if(!$("#del-link").hasClass('del-confirmed-drive-file')){
        $("#del-link").addClass('del-confirmed-drive-file');
    }
    $("#del-confirm .ctxt").text('this drive file');
    $("#del-confirm").modal('show');

});

$(document).on('click', '.del-confirmed-drive-file', function () { 

    var fileId = $(this).attr('data-file-id');
    var activeDriveId = $(this).attr('data-drive-id');

    var $this = $(this);
    $this.addClass('disabled');

    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: 'driveId='+activeDriveId+'&fileId='+fileId+'&type=del-drive-file',
        cache: false,
        success: function (data) {
            console.log(data);
            if(data.success)
            {
                var qsec = "tr[data-id='"+fileId+"']";
                $(qsec).remove();
                displayAlert(' <b>Alert: File deleted successfully !</b> ', 'success');
            }
            else
            {
                if(data.error != ''){
                    displayAlert(data.error, 'danger');
                }else{
                    displayAlert('<b>Alert: Unable to delete this file !</b>', 'danger');
                }
            }
            $this.removeClass('disabled');
            $("#del-confirm").modal('hide');
        },
        error: function (xhr) { // if error occured
            alert("Error occured.please try again");        
            $this.removeClass('disabled');
            $("#del-confirm").modal('hide');
        }
    });











 });


function getDriveId(){
    var driveId = $('#activeDriveId').attr('data-id');

    if(driveId !== undefined && driveId != ''){
        return driveId;
    }
    return '';
}
function getLinkId(){
    var linkId = $('#linkId').attr('data-id');

    if(linkId !== undefined && linkId != ''){
        return linkId;
    }
    return '';
}

function getFolderId(){
    var driveId = $('#folderId').attr('data-id');

    if(driveId !== undefined && driveId != ''){
        return driveId;
    }
    return '';
}


$(document).on('click', '.change-shared-status', function () { 

    var $this = $(this);
    var loader = '<div class="spinner-border" role="status"></div>';

    var lockedIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z"></path><rect x="5" y="11" width="14" height="10" rx="2"></rect><circle cx="12" cy="16" r="1"></circle><path d="M8 11v-4a4 4 0 0 1 8 0v4"></path></svg>';
    var unlockedIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z"></path><rect x="5" y="11" width="14" height="10" rx="2"></rect><circle cx="12" cy="16" r="1"></circle><path d="M8 11v-5a4 4 0 0 1 8 0"></path></svg>';
    var cIcon = $this.html();
    var cStatus = $this.attr('data-val');
    var activeDriveId = $('#activeDriveId').attr('data-id');
    var fileId = $this.parent().parent().attr('data-id');

    $this.html(loader);


    

    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: 'driveId='+activeDriveId+'&fileId='+fileId+'&status='+cStatus+'&type=change-drive-permission',
        cache: false,
        success: function (data) {
            console.log(data);
            if(data.success)
            {
                if(cStatus == 'shared'){
                    $this.removeClass('text-warning');
                    $this.addClass('text-secondary');
                    $this.attr('data-val','not-shared');
                    $this.html(lockedIcon);
                }else{
                    $this.removeClass('text-secondary');
                    $this.addClass('text-warning');
                    $this.attr('data-val','shared');
                    $this.html(unlockedIcon);
                }
            }
            else
            {
                alert(data.error);  
                $this.html(cIcon);
            }
        },
        error: function (xhr) { // if error occured
            alert("Error occured.please try again");        
            $this.html(cIcon);   
        }
    });


});

$('#change-drive-account').on('change', function() {
    window.location.href = this.value;
  });




$(document).on('click', '.add-sub', function () { 

    var elmt = $("#add-sub-dumy").clone();
    var nm = $("#sub-list .row").length + 1;
    elmt.attr('id','');
    elmt.find('input').val('');
    elmt.find('select').attr('name','sub['+nm+'][label]');
    elmt.find('input').attr('name','sub['+nm+'][file]');
    elmt.find('.remove-sub').removeClass('d-none');

    elmt.find('.sub-label, .sub-file, .download, .is_remove_sub').remove(); 

    $("#sub-list").append(elmt);

});

$(document).on('click', '.add-alt-link', function () { 

    var elmt = $("#add-alt-link-dumy").clone();
    var nm = $("#alt-links .row").length + 1;
    elmt.attr('id','');
    elmt.find('input').val('');
    elmt.find('input').attr('name','alt['+nm+'][link]');
    elmt.find('.remove-alt-link').removeClass('d-none');

    elmt.find('.is_remove_alt_link, .alt_link_id, .ibtn, .input-group-text').remove(); 

    $("#alt-links").append(elmt);

});

$(document).on('click', '.remove-alt-link', function () { 

    if($(this).parent().parent().find('.is_remove_alt_link').length > 0)
    {
        $(this).parent().parent().addClass('d-none');
        $(this).parent().parent().find('.is_remove_alt_link').val(1);
    }else{
        $(this).parent().parent().remove();
    }
    
});


$(document).on('click', '.remove-sub', function () { 
    if($(this).parent().parent().find('.is_remove_sub').length > 0)
    {
        $(this).parent().parent().addClass('d-none');
        $(this).parent().parent().find('.is_remove_sub').val(1);
    }
    else
    {
        $(this).parent().parent().remove();
    }
});

$(".remove-preview-img").on('click', function(){
    $(".preview-img-wrap").append('<input type="text" name="pre_img_del" hidden >');
    $(".preview-img-wrap").addClass('d-none');
});


$(".edit-server").on('click', function(){

    $('#form-server').trigger("reset");

    var elmt = $(this).parent().parent().parent();
    var id = elmt.attr('data-id');
    var name = elmt.find('.server-name').text();
    var type = elmt.find('.server-type span').text();
    var domain = elmt.find('.server-domain').text();
    
    $("#server-id").val(id);
    $("#server-name").val(name);
    $("#server-type").val(type);
    $("#server-domain").val(domain);

});

$(".del-server").on('click', function(){
    
    var link = $(this).attr('data-url');
    $("#del-link").attr('href',link);
    $("#del-confirm .ctxt").text('this server');
    $("#del-confirm").modal('show');

});

$(".del-gdrive-acc").on('click', function(){
    
    var link = $(this).attr('data-url');
    $("#del-link").attr('href',link);
    $("#del-confirm .ctxt").text('this account');
    $("#del-confirm").modal('show');

});


$(".del-all-backup-links").on('click', function(){
    
    var link = $(this).attr('data-url');
    $("#del-link").attr('href',link);
    $("#del-confirm .ctxt").text('that all backup links');
    $("#del-confirm").modal('show');

});

$(".del-gauth").on('click', function(){
    
    var link = $(this).attr('data-url');
    $("#del-link").attr('href',link);
    $("#del-confirm .ctxt").text('this drive account');
    $("#del-confirm").modal('show');

});


$(".refresh-server").on('click', function(){
    
    var elmt = $(this).parent().parent().parent();
    var id = elmt.attr('data-id');

    if(!$(this).hasClass('spin'))
    {
        $(this).addClass('spin');
    }
    var $this = $(this);

    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: 'id='+id+'&type=refresh-server',
        cache: false,
        success: function (data) {
            console.log(data);
            if(data.success)
            {
                displayAlert(' <b>Server status updated -> SUCCESS !</b>', 'success');
            }
            else
            {
                displayAlert(' <b>Server status updated -> FAILED !</b>', 'danger');
                // displayAlert(' <b>GDplyr is up to date :)</b>', 'success');
            }
            $this.removeClass('spin');
        },
        error: function (xhr) { // if error occured
            alert("Error occured.please try again");
            $this.removeClass('spin');
           
        }
    });
    

});
// console.log("%cFUCK YOU!", "color: red;font-size:128px; font-weight:bold"); 


$(".refresh-gauth").on('click', function(){
    
    var id = $(this).attr('data-id');

    if(!$(this).hasClass('spin'))
    {
        $(this).addClass('spin');
    }
    var $this = $(this);

    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: 'id='+id+'&type=refresh-gauth',
        cache: false,
        success: function (data) {
            console.log(data);
            if(data.success)
            {
                if(!$('.sd-'+id).hasClass('bg-green-lt'))
                {
                    $('.sd-'+id).removeClass('bg-red-lt');
                    $('.sd-'+id).addClass('bg-green-lt');
                    $('.sd-'+id).text('Active');
                }

                displayAlert(' <b>GDrive auth status updated -> SUCCESS !</b>', 'success');
            }
            else
            {
                if(!$('.sd-'+id).hasClass('bg-red-lt'))
                {
                    $('.sd-'+id).removeClass('bg-green-lt');
                    $('.sd-'+id).addClass('bg-red-lt');
                    $('.sd-'+id).text('Broken');
                }
                displayAlert(' <b>GDrive auth status updated -> FAILED !</b>', 'danger');
            }
            $this.removeClass('spin');
        },
        error: function (xhr) { // if error occured
            alert("Error occured.please try again");
            $this.removeClass('spin');
           
        }
    });
    

});


$('#select-tags-advanced').selectize({
    maxItems: 15,
    plugins: ['remove_button'],
});


function displayAlert(msg , type, append = false)
{
  
                              
                          
  var html = '<div class="alert alert-'+type+' alert-dismissible" role="alert">';

  html += msg +'  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';

  html += '</div>  ';
if(append){
    $("#alert-wrap").append(html);
}else{
    $("#alert-wrap").html(html);
}
 

}





   $( function() {
    $( "#alt-link-list, #sub-list, #alt-links" ).sortable({
        handle: ".move",
    });
    // $( "#alt-link-list, #sub-list" ).disableSelection();
  } );


  
$(document).on('click', '.copy-plyr-link2', function () {
    var $this = $(this);
    $this.text('copied');
    var url = $(this).parent().find('.plyr-link').text();
    copyToClipboard(url);

    setTimeout(function() { 
        var t = 'copy';
        $this.text(t);
    }, 2000);

});


$(document).on('click', '.copy-plyr-link', function () {
    var $this = $(this);
    $this.attr('data-original-title','copied');
    var url = $(this).attr('data-url');
    copyToClipboard(url);
    $this.tooltip('show');

    setTimeout(function() { 
        var t = 'copy player link';
        $this.attr('data-original-title',t);
    }, 1500);

});

$(document).on('click', '.copy-embed-code', function () {
    var $this = $(this);
    $this.attr('data-original-title','copied');
    var url = $(this).attr('data-url');
    var embed = '<iframe src="'+url+'" frameborder="0" allowFullScreen="true" width="640" height="320"></iframe>';
    copyToClipboard(embed);
    $this.tooltip('show');
    setTimeout(function() { 
        var t = 'copy embed code';
        $this.attr('data-original-title',t);
    }, 1500);
    
});



function copyToClipboard(text) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(text).select();
    document.execCommand("copy");
    $temp.remove();
}


$(".del-link").on('click', function(){
    
    var id = $(this).attr('data-id');
    $("#del-link").attr('data-id',id);

    if(!$("#del-link").hasClass('s-del-link'))
    {
        $("#del-link").addClass('s-del-link');
    }
    
    $("#del-confirm .ctxt").text('this link');
    $("#del-confirm").modal('show');

});


$(document).on('click', '.s-del-link', function () {
  
    var id = $(this).attr('data-id');
    var $this = $(this);
    $this.attr('disabled','disabled');
    $.ajax({
        type: "GET",
        url: PROOT + '/ajax',
        data: 'id=' + id + '&type=delete-link',
        cache: false,
        success: function (data) {
            console.log(data);
            if(data.success)
            {
                $('#link-'+id).remove();
            }
            $("#del-confirm").modal('hide');
            $this.removeAttr('disabled');
            
            $('#delete-confirmation').modal('hide');
        },
        error: function (xhr) { // if error occured
            alert("Error occured.please try again");
            $this.removeAttr('disabled');
            $("#del-confirm").modal('hide');
        }
    });


});



  $('.datatable').DataTable({
    "order": [],
    "columnDefs": [ {
      "targets": 'no-sort',
      "orderable": false,
} ]
} );





function get_b_pen_links(){
    var lc = $('.p-links').text();
    if(lc == undefined || lc == 0){
        lc = 0;
    }
    return parseInt(lc);
}

function get_b_tot_links(){
    var lc = $('.t-links').text();
    if(lc == undefined || lc == 0){
        lc = 0;
    }
    return parseInt(lc);
}





    $("#import-link").on('click', function(){

        var linkList = $("#link-list").val().split('\n');
    
        var links = [];
    
        for (var i=0; i < linkList.length; i++) {
            if (/\S/.test(linkList[i])) {
                links.push($.trim(linkList[i]));
            }
        }
    
    
        if(links.length > 0)
        {
            var totalLinks = links.length;
            $('.t-links, .p-links').text(totalLinks);
            importing();
            addLinks(links);
    
            $('.df').removeClass('d-none');
    
        }
        
    });
    

function importing()
{
    var html = '<div class="spinner-border spinner-border-sm text-white" role="status"><span class="sr-only">Loading...</span></div>&nbsp;Importing';
    $("#import-link").html(html);
    $("#processing-alert").show();
    $("#import-link, #link-list").attr('disabled','disabled');
   
}



function addLinks(links)


{


    if(typeof links[0] !== 'undefined') {
        
        var v_url = links[0];
        links.splice(0,  1);
        var driveId = getDriveId();


        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'url=' + v_url + '&driveId='+driveId+'&type=import-link',
            cache: false,
            success: function (data) {
                console.log(data);
                if(data.success)
                {
                    if(data.title.length == 0)
                    {
                        data.title = v_url ;
                    }
                    bi_add_response(data.title, 'success' , data.plyr);
                }
                else
                {
                    bi_add_response(v_url , 'danger',data.error);
                }
                updateImportStatus(data.success);
                addLinks(links);
                
            },
            error: function (xhr) { // if error occured
                console.log("Error occured.please try again. -> " + v_url );
                updateImportStatus(false);
                addLinks(links);
              
            }
        });


    }
    else {
        // does exist
        imported();
    }




}



$(document).on('click', '.edit-vast', function () {
  

    var vid = $(this).attr("data-id");
    var vtitle = $(this).attr("data-title");
    var voffset = $(this).attr("data-offset");
    var vskipoffset = $(this).attr("data-skipoffset");
    var vtype = $(this).attr("data-type");
    var vfile = $(this).attr("data-file");

    $("#vast-id").val(vid);
    $("#vast-title").val(vtitle);
    $("#vast-offset").val(voffset);
    $("#vast-file").val(vfile);
    $('#vast-type option[value="'+vtype+'"]').attr("selected", "selected");

    if(vtype == 'video')
    {
        $("#vast-offset").val(vskipoffset);
        $(".skipoff-input").removeClass('d-none');
    }

   



});


$('#vast-type').on('change', function () {
    //ways to retrieve selected option and text outside handler
    if(this.value == 'video')
    {
        $(".skipoff-input").removeClass('d-none');
    }
    else
    {
        if(!$('.skipoff-input').hasClass('d-none'))
        {
            $(".skipoff-input").addClass('d-none');
        }
    }
  });
















function imported()
{
    var html = 'Import';
    $("#import-link").html(html);

    if(get_b_pen_links() == 0){
        $("#processing-alert").hide();
    }
    $("#import-link, #link-list").removeAttr('disabled');
}

function bi_add_response(msg, type='',  error ='')
{

    if(type == 'danger')
    { 
        mtype = 'failed';
    }
    else
    {
        mtype = 'success';
    }

    var html = '<li class="list-group-item" style="    display: list-item;"> '+msg+ '<b class="float-right text-'+type+'" >'+mtype+'</b> ';
    if(type == 'danger')
    {
        html += '<br> <small class="text-danger">'+error+'</small>';
    }
    else
    {
        html += '<br><small> <span class="badge bg-blue">Player URL :  </span>&nbsp; <span class="plyr-link">'+error+'</span>  &nbsp;<a href="javascript:void(0)" class="text-info copy-plyr-link2" > <b>copy</b> </a></small>    ';
    }
    html += '  </li>';
    
    $("#mi-response").append(html);
}


function updateImportStatus(success = false)
{
    var p_link = $('.p-links').text();
    var s_link = $('.s-links').text();
    var f_link = $('.f-links').text();



    if(p_link != 0)
    {
        $('.p-links').text(parseInt(p_link)-1);
    }
if(success)
{
    $('.s-links').text(parseInt(s_link)+1);
}
else
{
    $('.f-links').text(parseInt(f_link)+1);
}
   


}

$(document).on('click', '#clear-logs' ,function() {
    $("#mi-response").html('');

});





$(document).on('change', '.delete-item' ,function() {
    if($(this).prop('checked')) {
            $(this).parent().parent().addClass('selected-for-delete');
    } else {
            $(this).parent().parent().removeClass('selected-for-delete');
    }
    upDel();

});


function upDel(){
    var selected = 0;
    selected = $(".selected-for-delete").length;
    if(selected != 0){
            $(".delete-selecetd-items").removeClass('d-none');
            $("#backup-multiple").removeClass('disabled');
    }else{
            $(".delete-selecetd-items").addClass('d-none');
            $("#backup-multiple").addClass('disabled');
    }
    $(".delete-selecetd-items b").text(selected);
}

$(document).on('click', '.delete-selecetd-items' ,function() {

    $('#del-confirm .ctxt').text('selected links');
    $("#del-confirm .dlo").attr('id','delete-selecetd-items');
    $("#del-confirm").modal('show');


});

$(document).on('click', '#delete-selecetd-items' ,function() {
        var ids = '';
        $('.selected-for-delete').each(function(i, obj) {
                ids += $(this).attr('data-id') + ',';
        });

        var $this= $(this);
        $this.text('Please wait...');
        $this.attr('disabled','disabled');

        
        var data = 'ids=' + ids + '&type=delete-link-list';

        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: data,
            cache: false,
            success: function (data) {
                if (data.success) {
                    //success
                } else {
                    alert('Can not delete this links !');
                }
                location.reload();
            },
            error: function (xhr) { // if error occured
                alert("Error occured.please try again");
                location.reload();
            }


        });




});


$("#select_all").change(function() {

    // var movieId = $(this).attr('data-movie-id');

    $('.delete-item').parent().parent().removeClass('selected-for-delete');
    if($(this).prop('checked')) {
            $('.delete-item').prop('checked', true);
            $('.delete-item').parent().parent().addClass('selected-for-delete');

    } else {
            $('.delete-item').prop('checked', false);

    }
    upDel();
});









$("#clear-cache").on('click', function(){


   var html = '<div class="spinner-border spinner-border-sm text-white" role="status"><span class="sr-only">Loading...</span></div>&nbsp;please wait...';
    $(this).html(html);
    var $this = $(this);
    $this.attr('disabled','disabled');


        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'type=clear-cache',
            cache: false,
            success: function (data) {
                
                $("#cache-size").text('0 B');
                $this.html('clear cache');
                
                
            },
            error: function (xhr) { // if error occured
                console.log("Error occured.please try again. -> " + v_ip );
                
              
            }
        });



});


$("#removeLogo").on('click', function(){
    $("#logoVal").val('');
    $("#logoImg").remove();
    $(this).remove();
});

$("#removeFav").on('click', function(){
    $("#favVal").val('');
    $("#favIco").remove();
    $(this).remove();
});



$("#check-proxy").on('click', function(){

    var proxyList = $("#proxy-list").val().split(',');

    var proxy = [];



    for (var i=0; i < proxyList.length; i++) {
        if (/\S/.test(proxyList[i])) {
            proxy.push($.trim(proxyList[i]));
        }
    }


    if(proxy.length > 0)
    {
        var totalProxy = proxy.length;
        $('.t-proxy').text(totalProxy);
        // $('.t-links, .p-links').text(totalLinks);
        checking();
        checkProxy(proxy);

        $('.proxy-progress').removeClass('d-none');

       console.log(proxy);

    }
    
});






function checkProxy(proxy)
{

    if(typeof proxy[0] !== 'undefined') {

        var v_ip = proxy[0];
        proxy.splice(0,  1);

        $.ajax({
            type: "GET",
            url: PROOT + '/ajax',
            data: 'ip=' + v_ip + '&type=check-proxy',
            cache: false,
            success: function (data) {
                updateCheckedStatus();
                checkProxy(proxy);
                
                
            },
            error: function (xhr) { // if error occured
                console.log("Error occured.please try again. -> " + v_ip );
                updateCheckedStatus();
                checkProxy(proxy);
              
            }
        });


    }
    else {
        // does exist
        checked();
        window.location.reload();


    }




}




function updateCheckedStatus()
{
    var p_proxy = $('.p-proxy').text();
    var t_proxy = $('.t-proxy').text();

    if(p_proxy != t_proxy)
    {
        $('.p-proxy').text(parseInt(p_proxy)+1);

      
        var st = Math.round(((parseInt(p_proxy) + 1) * 100) / parseInt(t_proxy));
        $(".p-valume").text(st + '% completed');
       


        $(".progress .progress-bar").attr('style', 'width:' + st + '%');
        $(".progress .progress-bar").attr('aria-valuenow', st);

        

        



    }
   



}






function checking()
{
    var html = '<div class="spinner-border spinner-border-sm text-white" role="status"><span class="sr-only">Loading...</span></div>&nbsp;checking...';
    $("#check-proxy").html(html);
    $("#check-proxy").attr('disabled','disabled');
}




function checked()
{
    var html = 'Check proxies';
    $("#check-proxy").html(html);
    $("#check-proxy").removeAttr('disabled');
}


















$("#copyStreamLink").on('click', function(){
    var txt = $("#streamLink").val();
    var $this = $(this);
    copyToClipboard(txt);
    $this.text('copied');
    setTimeout(
        function()
        { 
            $this.text('copy');
         }, 2000
    );
});

$("#copyEmbedCode").on('click', function(){
    var txt = $("#embedCode").val();
    var $this = $(this);
    copyToClipboard(txt);
    $this.text('copied');
    setTimeout(
        function()
        { 
            $this.text('copy');
         }, 3000
    );
});

$("#copyPlyrLink").on('click', function(){
    var txt = $("#plyrLink").val();
    var $this = $(this);
    copyToClipboard(txt);
    $this.text('copied');
    setTimeout(
        function()
        { 
            $this.text('copy');
         }, 3000
    );
});