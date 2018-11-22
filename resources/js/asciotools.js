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
class AscioInstaller {
    update(nr) {
        var self = this;
        if(!nr) nr = 0;         
        var element = $(".update-action")[nr]
        if(element) {
            element = $(element);
            var action = element.data("action");
            var icon = $("#icon-"+action);
            icon.removeClass("glyphicon-remove");
            icon.addClass("glyphicon-time");
            icon.attr("style","color:black");    
            $.ajax({
                url: "../modules/addons/asciotools/ssl/Installer/install.php",
                datatype : "json",
                data: { 
                    "action": element.data("action"),
                    "local-path" : element.data("local-path"),
                    "git": element.data("git"),
                    "module": element.data("module"),
                }              
              }).done(function(data) {            
                if(data.error) {
                    element.html('<div style="color:darkred" role="alert">'+data.error+'</div>');
                    icon.addClass("glyphicon-remove");
                    icon.removeClass("glyphicon-time");
                    icon.attr("style","color:darkred");                    
                } else {
                    element.html('<div style="color:darkgreen" role="alert">OK</div>');
                    icon.addClass("glyphicon-ok");
                    icon.removeClass("glyphicon-remove");
                    icon.attr("style","color:darkgreen");
                    $("#text-"+action).attr("style","color:darkgreen");
                    self.update(nr+1);
                }
                
              }); 

        }
        
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
    $("#update").click(function() {
        ascioInstaller = new AscioInstaller();
        ascioInstaller.update();
    });
})