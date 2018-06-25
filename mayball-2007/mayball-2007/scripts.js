window.onload = loadHandlers;

var i = 2;
var changed = false;

function loadHandlers() {
  update_numguests();
  document.getElementById('addguest').onclick = function(){ AddText(); };
  document.getElementById('firstName').onchange = function(){ CopyFields(); };
  document.getElementById('surname').onchange = function(){ CopyFields(); };
  document.getElementById('gFirstName1').onchange = function(){ changed = true; };
  //document.getElementById('gSurname1').onchange = function(){ changed = true; };
  document.getElementById('submit_button').onclick = function(){ submitForm(); };
}

function submitForm() {
  var j;
  document.getElementById("gFirstNames").value = "";
  document.getElementById("gTicketTypes").value = "";
  for (j=1;j<i;j++) {
    document.getElementById("gFirstNames").value = document.getElementById("gFirstNames").value + document.getElementById("gFirstName"+j).value + ",";
    document.getElementById("gTicketTypes").value = document.getElementById("gTicketTypes").value + document.getElementById("gTicketType"+j).value + ",";
  }
  document.ticket_form.submit();
}

function CopyFields() {
  if (changed == false) {
    document.getElementById('gFirstName1').value = document.getElementById('firstName').value + " " + document.getElementById('surname').value;
  }
}
function AddText() {
            
  var row = document.getElementById("guesttable").insertRow(-1);
  var cell;
  
  cell = row.insertCell(-1);
  cell.innerHTML = i;
   
  cell = row.insertCell(-1);  
  new_input("gFirstName", cell);
  
     
  cell = row.insertCell(-1);
  
  var select = document.createElement("select");
  select.name="gTicketType" + i;
  select.id=select.name
  cell.appendChild(select);
  
  add_option("Normal", "Normal", select);
  add_option("Priority", "Priority Queuing", select);
  add_option("Dining", "Dining", select);
  
  i++;
  
  update_numguests();
}

function update_numguests() {
  document.getElementById("numTickets").value = i-1;
}

function new_input(name, parent) {
  var input = document.createElement("input");
  input.type="text";
  input.name=name + i;
  input.id=input.name
  input.size=40;
  parent.appendChild(input);
}

function add_option(value, label, select) {
  var option = document.createElement("option");
  option.value=value;
  select.appendChild(option);
  
  var text = document.createTextNode(label);
  option.appendChild(text);
}
