/**
 * assets/js/editor.js - Logique de manipulation des blocs et requêtes AJAX pour l'éditeur
 */

let blocks = [];

document.addEventListener('DOMContentLoaded', () => {
    if (typeof initialData !== 'undefined' && Array.isArray(initialData)) {
        blocks = initialData;
    }
    renderCanvas();
});

function addBlock(type) {
    let newBlock = { type: type };
    
    switch(type) {
        case 'header':
            newBlock.content = 'Nom de votre entreprise / Logo';
            break;
        case 'title':
            newBlock.content = 'Titre principal de l\'e-mail';
            break;
        case 'text':
            newBlock.content = 'Rédigez votre paragraphe de texte ici...';
            break;
        case 'button':
            newBlock.text = 'Cliquez ici';
            newBlock.url = 'https://example.com';
            break;
        case 'image':
            newBlock.url = 'https://via.placeholder.com/600x200';
            break;
        case 'spacer':
            break;
    }
    
    blocks.push(newBlock);
    renderCanvas();
}

function removeBlock(index) {
    blocks.splice(index, 1);
    renderCanvas();
}

function moveBlock(index, direction) {
    const newIndex = index + direction;
    if (newIndex >= 0 && newIndex < blocks.length) {
        const temp = blocks[index];
        blocks[index] = blocks[newIndex];
        blocks[newIndex] = temp;
        renderCanvas();
    }
}

function updateBlockContent(index, field, value) {
    blocks[index][field] = value;
}

function renderCanvas() {
    const canvas = document.getElementById('email-canvas');
    
    let html = '<div class="email-container-mock">';
    
    if (blocks.length === 0) {
        html += '<p style="text-align: center; color: #aaa; margin: 40px 0;">Le template est vide. Ajoutez des blocs depuis la barre latérale.</p>';
    } else {
        blocks.forEach((block, index) => {
            html += `<div class="block-item">`;
            html += `<div class="block-controls">`;
            if (index > 0) html += `<button onclick="moveBlock(${index}, -1)" title="Monter">&uarr;</button>`;
            if (index < blocks.length - 1) html += `<button onclick="moveBlock(${index}, 1)" title="Descendre">&darr;</button>`;
            html += `<button onclick="removeBlock(${index})" title="Supprimer">&times;</button>`;
            html += `</div>`;
            
            switch(block.type) {
                case 'header':
                    html += `<strong>[En-tête]</strong>`;
                    html += `<input type="text" class="block-edit-field" value="${escapeHtml(block.content || '')}" oninput="updateBlockContent(${index}, 'content', this.value)">`;
                    break;
                case 'title':
                    html += `<strong>[Titre H1]</strong>`;
                    html += `<input type="text" class="block-edit-field" value="${escapeHtml(block.content || '')}" oninput="updateBlockContent(${index}, 'content', this.value)">`;
                    break;
                case 'text':
                    html += `<strong>[Paragraphe]</strong>`;
                    html += `<textarea class="block-edit-field" rows="3" oninput="updateBlockContent(${index}, 'content', this.value)">${escapeHtml(block.content || '')}</textarea>`;
                    break;
                case 'button':
                    html += `<strong>[Bouton CTA]</strong>`;
                    html += `<div style="display: flex; gap: 5px; margin-top: 5px;">`;
                    html += `<input type="text" class="block-edit-field" placeholder="Texte du bouton" value="${escapeHtml(block.text || '')}" oninput="updateBlockContent(${index}, 'text', this.value)">`;
                    html += `<input type="text" class="block-edit-field" placeholder="URL" value="${escapeHtml(block.url || '')}" oninput="updateBlockContent(${index}, 'url', this.value)">`;
                    html += `</div>`;
                    break;
                case 'image':
                    html += `<strong>[Image]</strong>`;
                    html += `<input type="text" class="block-edit-field" placeholder="URL de l'image" value="${escapeHtml(block.url || '')}" oninput="updateBlockContent(${index}, 'url', this.value)">`;
                    break;
                case 'spacer':
                    html += `<strong>[Séparateur horizontal]</strong>`;
                    break;
            }
            
            html += `</div>`;
        });
    }
    
    html += '</div>';
    canvas.innerHTML = html;
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function saveTemplate(id) {
    const name = document.getElementById('template-name').value;
    const subject = document.getElementById('email-subject').value;
    
    const data = {
        id: id,
        name: name,
        subject: subject,
        blocks: blocks
    };
    
    fetch('ajax/save-template.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Template enregistré avec succès !');
            if (!id && result.id) {
                window.location.href = 'editor.php?id=' + result.id;
            }
        } else {
            alert('Erreur lors de l\'enregistrement : ' + (result.error || 'Inconnue'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur réseau lors de l\'enregistrement.');
    });
}