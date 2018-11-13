class AscioImporter {
    getCertIds () {
        var certIds=[];
        $(".cert-select").each(function(nr,checkbox) {
            var cb = $(checkbox);
            if(checkbox.checked) {
                certIds.push($(checkbox).data("id"));
            }
            
        });
        return certIds;

    }
    calulateSsl () {
        var certIds=this.getCertIds();
        $.ajax({
            url: "../modules/addons/asciotools/ssl/import.php?action=preview",
            datatype : "json",
            data: { 
                margin: $("#margin").val(),
                round: $("#round").val(),
                products: certIds 
            }
          }).done(function(data) {            
            $("#preview").html(data.html)
          });          
    } 
    importSsl() {
        var certIds=this.getCertIds();
        $.ajax({
            url: "../modules/addons/asciotools/ssl/import.php?action=import",
            datatype : "json",
            data: { 
                products: certIds,
            }
          }).done(function(data) {            
            var d = new Date();
            console.log(certIds.length);
            $("#preview").html('<div class="alert alert-success" role="alert">['+d.toLocaleString()+'] <b>'+certIds.length+' Products imported!</b></div>' + data.html)
          }); 
    }
}

jQuery(document).ready(function(){
    var ascioImporter = new AscioImporter();
    $("#calculate").click(function() {
        ascioImporter.calulateSsl()
    });
    $("#upload").click(function() {
        ascioImporter.importSsl();        
    });
})