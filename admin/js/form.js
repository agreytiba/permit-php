// script.js
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addToolButton').addEventListener('click', function() {
        const toolsContainer = document.getElementById('toolsContainer');
        const newToolInput = document.createElement('div');
        newToolInput.classList.add('toolInput', 'flex', 'flex-col', 'space-y-2', 'mb-4');
        newToolInput.innerHTML = `
            <label for="toolName" class="text-sm">Tool/Equipment Name</label>
            <input type="text" name="toolName[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
            <label for="toolStatus" class="text-sm">Tool/Equipment Status</label>
            <input type="text" name="toolStatus[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
            <label for="toolDocument" class="text-sm">Tool/Equipment Document (PDF/Picture)</label>
            <input type="file" name="toolDocument[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
        `;
        toolsContainer.appendChild(newToolInput);
    });
});


