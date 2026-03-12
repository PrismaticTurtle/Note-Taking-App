/**
 * Note Taking App - Main Application
 */

const API_BASE = 'api.php';
let currentNoteId = null;
let allNotes = [];

// DOM Elements
const newNoteBtn = document.getElementById('newNoteBtn');
const searchInput = document.getElementById('searchInput');
const notesList = document.getElementById('notesList');
const emptyState = document.getElementById('emptyState');
const editor = document.getElementById('editor');
const noteTitle = document.getElementById('noteTitle');
const noteContent = document.getElementById('noteContent');
const saveBtn = document.getElementById('saveBtn');
const deleteBtn = document.getElementById('deleteBtn');
const createdDate = document.getElementById('createdDate');
const updatedDate = document.getElementById('updatedDate');

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadNotes();
    attachEventListeners();
});

/**
 * Attach event listeners
 */
function attachEventListeners() {
    newNoteBtn.addEventListener('click', createNewNote);
    saveBtn.addEventListener('click', saveCurrentNote);
    deleteBtn.addEventListener('click', deleteCurrentNote);
    searchInput.addEventListener('input', debounce(handleSearch, 300));

    // Auto-save when text changes (optional - comment out to disable)
    // noteTitle.addEventListener('change', saveCurrentNote);
    // noteContent.addEventListener('change', saveCurrentNote);
}

/**
 * Load all notes
 */
async function loadNotes() {
    try {
        const response = await fetch(`${API_BASE}?action=list`);
        if (!response.ok) throw new Error('Failed to load notes');

        const data = await response.json();
        allNotes = data.notes;
        renderNotesList(allNotes);
    } catch (error) {
        console.error('Error loading notes:', error);
        showNotification('Failed to load notes', 'error');
    }
}

/**
 * Render notes list
 */
function renderNotesList(notes) {
    notesList.innerHTML = '';

    if (notes.length === 0) {
        notesList.innerHTML = '<li style="padding: 20px; text-align: center; color: #999;">No notes found</li>';
        return;
    }

    notes.forEach(note => {
        const li = document.createElement('li');
        li.className = 'note-item';
        if (note.id === currentNoteId) li.classList.add('active');

        li.innerHTML = `
            <span class="note-item-title">${escapeHtml(note.title)}</span>
            <span class="note-item-date">${formatDate(note.updated_at)}</span>
        `;

        li.addEventListener('click', () => loadNote(note.id));
        notesList.appendChild(li);
    });
}

/**
 * Load a specific note
 */
async function loadNote(id) {
    try {
        const response = await fetch(`${API_BASE}?action=read&id=${id}`);
        if (!response.ok) throw new Error('Failed to load note');

        const note = await response.json();
        currentNoteId = note.id;

        // Update UI
        noteTitle.value = note.title;
        noteContent.value = note.content;
        createdDate.textContent = `Created: ${formatDateLong(note.created_at)}`;
        updatedDate.textContent = `Updated: ${formatDateLong(note.updated_at)}`;

        // Toggle visibility
        emptyState.classList.add('hidden');
        editor.classList.remove('hidden');

        // Update active state
        renderNotesList(allNotes);
    } catch (error) {
        console.error('Error loading note:', error);
        showNotification('Failed to load note', 'error');
    }
}

/**
 * Create a new note
 */
async function createNewNote() {
    try {
        const response = await fetch(`${API_BASE}?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: 'Untitled Note',
                content: ''
            })
        });

        if (!response.ok) throw new Error('Failed to create note');

        const data = await response.json();
        await loadNotes();
        loadNote(data.id);
        showNotification('New note created');
    } catch (error) {
        console.error('Error creating note:', error);
        showNotification('Failed to create note', 'error');
    }
}

/**
 * Save current note
 */
async function saveCurrentNote() {
    if (!currentNoteId) return;

    const title = noteTitle.value.trim();
    const content = noteContent.value.trim();

    if (!title || !content) {
        showNotification('Title and content cannot be empty', 'error');
        return;
    }

    try {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        const response = await fetch(`${API_BASE}?action=update&id=${currentNoteId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, content })
        });

        if (!response.ok) throw new Error('Failed to save note');

        await loadNotes();
        await loadNote(currentNoteId);
        showNotification('Note saved successfully');
    } catch (error) {
        console.error('Error saving note:', error);
        showNotification('Failed to save note', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save';
    }
}

/**
 * Delete current note
 */
async function deleteCurrentNote() {
    if (!currentNoteId) return;

    if (!window.confirm('Are you sure you want to delete this note? This cannot be undone.')) {
        return;
    }

    try {
        deleteBtn.disabled = true;
        const response = await fetch(`${API_BASE}?action=delete&id=${currentNoteId}`, {
            method: 'DELETE'
        });

        if (!response.ok) throw new Error('Failed to delete note');

        currentNoteId = null;
        emptyState.classList.remove('hidden');
        editor.classList.add('hidden');
        await loadNotes();
        showNotification('Note deleted successfully');
    } catch (error) {
        console.error('Error deleting note:', error);
        showNotification('Failed to delete note', 'error');
    } finally {
        deleteBtn.disabled = false;
    }
}

/**
 * Handle search
 */
async function handleSearch(e) {
    const query = e.target.value.trim();

    if (query.length === 0) {
        renderNotesList(allNotes);
        return;
    }

    try {
        const response = await fetch(`${API_BASE}?action=search&q=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error('Search failed');

        const data = await response.json();
        renderNotesList(data.results);
    } catch (error) {
        console.error('Search error:', error);
        showNotification('Search failed', 'error');
    }
}

/**
 * Utility: Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Utility: Format date short
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
}

/**
 * Utility: Format date long
 */
function formatDateLong(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Utility: Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Utility: Show notification
 */
function showNotification(message, type = 'success') {
    // Simple console notification for now
    console.log(`[${type.toUpperCase()}] ${message}`);

    // Optional: You can replace this with a toast notification system
    // For now, we'll keep it simple
}
