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

function initTree(selector) {
  // Tree-Functions
  /**
 *
 */
  function mergeArray(var1, var2) {
    var d = var1.length;
    for(i = 0; i < d; i++) {
      if($.isArray(var2)) {
        // second parameter is array
        var x = var2.length;
        for(j = 0; j < x; j++) {
          if((var1[i].data != null && var2[j].data != null) && var1[i].data.toString() == var2[j].data.toString()) {
            // both data field aren't null and content is the same
            if(var1[i].children == null && var2[j].children != null) {
              // current element has no children.. so link children of new element to current element
              var1[i].children = var2[j].children;
            } else if(var1[i].children != null && var2[j].children != null) {
              // both elements has children. Merge both.
              mergeArray(var1[i].children, var2[j].children);
            }
            return 0;
          } else if(var2[j].data != null) {
            // is this the last element?
            if(i == (d-1)) {
              // yes, element was not in the array. Push it.
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

  function formatZoneArray(zone, c, full) {
    zone = zone.toString();
    rel = "ezone";
    if(c == 0) {
      rel = "izone";
    }
    c++;
    if(zone.lastIndexOf('.') == -1) {
      // Recursion End
      return {
        data: zone,
        //state: "closed",
        attr: {
          id: full,
          rel: rel
        }
      }
    } else {
      datastr = zone.substr(zone.lastIndexOf('.')+1, zone.length - zone.lastIndexOf('.'));
      reststr = zone.substr(0, zone.lastIndexOf('.'));
      return {
        data: datastr,
        //state: "closed",
        attr: {
          id: full,
          rel: rel
        },
        children: [formatZoneArray(reststr, c, full)]
      }
    }
  }

  // Tree
  $(selector).jstree({
    core: {
      animation: 10
    },
    plugins: ['json_data','types','themes', 'ui'],
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
                  id: "server-"+sysname,
                  rel: "server"
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
                fullstr = zone.name+"|"+tagid.substr(7,tagid.length);
                if(zones.length == 0) {
                  zones.push(formatZoneArray(zone.name, 0, fullstr));
                } else {
                  mergeArray(zones, formatZoneArray(zone.name, 0, fullstr));
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
      "valid_children": ["server"],
      "types": {
        "server": {
          "valid_children": ["ezone", "izone"],
          "open_node": function() {
            return true;
          },
          "select_node": false,
          "icon": {
            "image" : "css/jquery/images/server-icon.png"
          }
        },
        "ezone": {
          "valid_children": ["ezone", "izone"],
          "select_node": function(obj) {
            //var_dump(event.currentTarget,2);
            obj = this._get_node(obj);
            info = obj[0].id.split('|');
            recordUpdateList(info[1],info[0]);
            $("#treeToggleButton").click();
            return true;
          },
          "icon": {
            "image" : "css/jquery/images/ezone.png"
          }
        },
        "izone": {
          "valid_children": ["ezone", "izone"],
          "select_node": false,
          "icon": {
            "image" : "css/jquery/images/izone.png"
          }
        }
      }
    },
    "ui" : {
      "select_limit" : 1
    }
  });
}