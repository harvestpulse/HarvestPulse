const PRODUCE = [
  {e:'🍅',n:'Tomatoes'},{e:'🥬',n:'Spinach'},{e:'🌽',n:'Corn'},{e:'🎃',n:'Butternut'},
  {e:'🥕',n:'Carrots'},{e:'🥦',n:'Broccoli'},{e:'🧅',n:'Onions'},{e:'🥔',n:'Potatoes'},
  {e:'🫑',n:'Peppers'},{e:'🍆',n:'Brinjal'},{e:'🌿',n:'Herbs'},{e:'🍋',n:'Lemons'},
  {e:'🥭',n:'Mangoes'},{e:'🍓',n:'Berries'},{e:'🫛',n:'Peas'},{e:'🌰',n:'Nuts'},
  {e:'🍠',n:'Sweet pot'},{e:'🥒',n:'Cucumber'},{e:'🧄',n:'Garlic'},{e:'🌶️',n:'Chilli'}
];
let selProduce = 0, selWindow = '30min';

function selectProduce(element, cropName) {
  document.querySelectorAll('.produce-item').forEach(item => {
    item.classList.remove('sel');
  });

  element.classList.add('sel');
  document.getElementById('cropName').value = cropName;
}

function selectWindow(el, w) {
  selWindow = w;
  document.querySelectorAll('.window-btn').forEach(b => b.classList.remove('sel'));
  el.classList.add('sel');
}


function selectProduce(element, cropName) {
    document.querySelectorAll('.produce-item').forEach(item => {
        item.classList.remove('sel');
    });

    element.classList.add('sel');
    document.getElementById('cropName').value = cropName;
}

function selectWindow(element, windowValue) {
    document.querySelectorAll('.window-btn').forEach(btn => {
        btn.classList.remove('sel');
    });

    element.classList.add('sel');
    document.getElementById('auctionWindow').value = windowValue;
}