<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Note App</title>
</head>
<body>
    <h1>Add a New Note</h1>
    <form id="noteForm">
        <input type="text" id="noteTitle" placeholder="Title" required><br>
        <textarea id="noteContent" placeholder="Your note here..." required></textarea><br>
        <button type="submit">Save Note</button>
    </form>

    <script>
        document.getElementById('noteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const title = document.getElementById('noteTitle').value;
            const content = document.getElementById('noteContent').value;

            const response = await fetch('/add-note', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ title, content })
            });

            if (response.ok) alert('Note saved!');
            else alert('Error saving note.');
        });

        <div id="notesContainer"></div>

    <script>
    // Function to load and display notes
    async function loadNotes() {
        const response = await fetch('/notes');
        const notes = await response.json();
        
        const container = document.getElementById('notesContainer');
        container.innerHTML = '<h2>Your Notes</h2>';
        
        notes.forEach(note => {
            container.innerHTML += `
                <div class="note-card">
                    <h3>${note.title}</h3>
                    <p>${note.content}</p>
                </div>
            `;
        });
    }

    // Call this when the page loads
    loadNotes();

    // Update your existing form submission to refresh the list automatically
    document.getElementById('noteForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        // ... (previous form submission code) ...
        
        // After successful save, refresh the list
        await loadNotes();
        document.getElementById('noteForm').reset();
    });

        
    </script>
</body>
</html>