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

function recordUpdateList(server,zonename) {
	$("#recordTable tbody").empty();
	$.ajax({
		url: 'api/servers/'+server+'/zones/'+zonename+'/records',
		success: function(data) {
			for (recordid in data) {
				str = "<tr>";
				str += "<td>"+data[recordid].name+"</td>";
				str += "<td>"+data[recordid].type+"</td>";
				str += "<td>"+data[recordid].content+"</td>";
				str += "<td>"+data[recordid].ttl+"</td>";
				str += "<td>edit/delete</td>";
				str += "</tr>";
				$("#recordTable tbody").append(str);
			}
			$("#recordTable tbody").append();
			$("#recordTable").trigger("update");
			alert($("#recordTable").tablesorter.config.sortList);
		}
	});
}