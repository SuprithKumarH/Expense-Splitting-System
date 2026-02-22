// app.js
document.addEventListener('DOMContentLoaded', () => {
    console.log("Splitwise Clone Initialized");

    // Fetch groups for the select dropdown
    fetch('groups.php')
        .then(response => response.json())
        .then(groups => {
            const groupSelect = document.getElementById('group');
            if (groupSelect) {
                groups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    groupSelect.appendChild(option);
                });
            }
        });

    // Handle form submission for adding expense
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) {
        expenseForm.addEventListener('submit', event => {
            event.preventDefault();
            
            const formData = new FormData(expenseForm);
            
            fetch('add_expense.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                alert(result);
                // Reload or update the expense list
            });
        });
    }

    // Fetch expenses
    fetch('fetch_expenses.php')
        .then(response => response.json())
        .then(expenses => {
            const expenseList = document.getElementById('expenseList');
            if (expenseList) {
                expenses.forEach(expense => {
                    const li = document.createElement('li');
                    li.textContent = `${expense.description} - $${expense.amount} - ${expense.group_name} - ${expense.date}`;
                    expenseList.appendChild(li);
                });
            }
        });
});
