/*
 * This file is part of phpDNSAdmin.
 * (c) 2010 Matthias Lohr - http://phpdnsadmin.sourceforge.net/
 *
 * phpDNSAdmin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phpDNSAdmin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phpDNSAdmin. If not, see <http://www.gnu.org/licenses/>.
 */

function login(username,password) {
  $.ajax({
    url: 'api/status',
    data: {
      username: username,
      password: password
    },
    type: 'POST',
    success: function(data) {
      if (data.loggedIn == true) {
        performLogin();
      }
      else {
        performLogout();
      }
    },
    error: function(request,errorType,exception) {

    }
  });
}

function logout() {
  $.ajax({
    url: 'api/status',
    type: 'POST',
    data: {
      username: ''
    },
    dataType: 'json',
    success: function(data) {
      $("#loadingDialog").dialog("option","beforeclose","");
      $("#loadingDialog").dialog("close");
      if (data.loggedIn == true) {
        performLogin();
      }
      else {
        performLogout();
      }
    },
    error: function(request,errorType,exception) {

    }
  });
}

function performLogin() {
  $("#loginDialog").dialog("close");
}

function performLogout() {
  $("#loginDialog").dialog("open");
//$("#zoneSelector").jstree("create_node",$("#zoneSelector"),'inside',{data:'test234'});
}

function updateStatus() {
  $.ajax({
    url: 'api/status',
    type: 'GET',
    success: function(data) {
      $("#loadingDialog").dialog("option","beforeclose","");
      $("#loadingDialog").dialog("close");
      if (data.loggedIn == true) {
        performLogin();
      }
      else {
        performLogout();
      }
    },
    error: function(request,errorType,exception) {

    }
  });
}

function mergeArray(var1, var2) {
  var d = var1.length;
  for(i = 0; i < d; i++) {
    if($.isArray(var2)) {
      var x = var2.length;
      for(j = 0; j < x; j++) {
        if((var1[i].data != null && var2[j].data != null) && var1[i].data.toString() == var2[j].data.toString()) {
          if(var1[i].children == null && var2[j].children != null) {
            var1[i].children = var2[j].children;
          } else if(var1[i].children != null && var2[j].children != null) {
            mergeArray(var1[i].children, var2[j].children);
          }
          return 0;
        } else if(var2[j].data != null) {
          if(i == (d-1)) {
            var1.push(var2[j]);
          }
        }
      }
    } else {
      if((var1[i].data != null && var2.data != null) && var1[i].data.toString() == var2.data.toString()) {
        if(var1[i].children == null && var2.children != null) {
          var1[i].children = var2.children;
        } else if(var1[i].children != null && var2.children != null) {
          mergeArray(var1[i].children, var2.children);
        }
        return 0;
      } else if(var2.data != null) {
        if(i == (d-1)) {
          var1.push(var2);
        }
      }
    }
  }
}

function formatZoneArray(zone) {
  zone = zone.toString();
  if(zone.lastIndexOf('.') == -1) {
    // Recursion End
    return {
      data: zone,
      //state: "closed",
      attr: {
        id: "zones-"+zone
      }
    }
  } else {
    datastr = zone.substr(zone.lastIndexOf('.')+1, zone.length - zone.lastIndexOf('.'));
    reststr = zone.substr(0, zone.lastIndexOf('.'));
    return {
      data: datastr,
      //state: "closed",
      attr: {
        id: "zones-"+datastr
      },
      children: [formatZoneArray(reststr)]
    }
  }
}

// immediate execution
$(document).ready(function() {
  // set ajax default options
  $.ajaxSetup({
    type: 'GET',
    dataType: 'json'
  });
  //
  $("#loadingDialog").dialog({
    beforeclose: function() {
      return false
    },
    closeOnEscape: false,
    draggable: false,
    modal: true,
    resizable: false
  });
  updateStatus();
  $("#loginDialog").dialog({
    autoOpen: false,
    //beforeclose: function() {return false},
    buttons: {
      "Login": function() {
        username = $('#usernameInput').attr('value');
        password = $('#passwordInput').attr('value');
        $('#passwordInput').attr('value','')
        login(username,password);
      }
    },
    closeOnEscape: false,
    draggable: false,
    modal: true,
    resizable: false,
    width: '400px'
  });
  $("#mainPanel").tabs();
  // table
  $("#recordTable").tablesorter({
    sortList: [[0,0],[1,1]]
  });

  // Tree
  $("#zoneSelector").jstree({
    core: {
      animation: 10
    },
    plugins: ['json_data','types','themes'],
    json_data: {
      ajax: {
        url: function(node) {
          this.contextNode = node;
          if (node == -1) return "api/servers";
          tagid = node.attr('id');
          if (tagid.substr(0,7) == "server-") {
            return "api/servers/"+tagid.substr(7)+"/zones";
          }
          return "";
        },
        success: function(data) {
          if (this.contextNode == -1) {
            servers = [];
            for (sysname in data) {
              servers.push({
                data: data[sysname]['name'],
                state: "closed",
                attr: {
                  id: "server-"+sysname
                }
              });
            }
            return servers;
          }
          else {
            tagid = this.contextNode.attr('id');
            if (tagid.substr(0,7) == "server-") {
              zones = [];
              $(data).each(function(index, zone) {
                if(zones.length == 0) {
                  zones.push(formatZoneArray(zone.name));
                } else {
                  mergeArray(zones, formatZoneArray(zone.name));
                }
              });
              return zones;
            }
          }
          return {};
        }
      }
    },
    themes: {
      url: 'js/jstree-themes/default/style.css'
    },
    types: {
      server: {
        valid_children: ['ezone','izone'],
        open_node: function(arg1) {
          alert(arg1);
        }
      },
      ezone: {
        valid_children: ['ezone','izone']
      },
      izone: {
        valid_children: ['ezone','izone']
      }
    }
  });
//$("#recordTable").trigger("sorton",[[[0,0],[1,1]]]);
});