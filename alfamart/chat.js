import { GoogleGenerativeAI } from "https://esm.sh/@google/generative-ai";

const GEMINI_API_KEY = "AIzaSyBwMlI_shefQ1EsDjQZcIVxrmCRB6ew2Z8";
const genAI = new GoogleGenerativeAI(GEMINI_API_KEY);

const chatWidget = document.getElementById('chat-widget');
const chatToggle = document.getElementById('chat-toggle');
const chatClose = document.getElementById('chat-close');
const chatMessages = document.getElementById('chat-messages');
const chatInput = document.getElementById('chat-input');
const chatSend = document.getElementById('chat-send');

let isChatOpen = false;

// System context for Gemini
const SYSTEM_CONTEXT = "Anda adalah Asisten AI untuk AlfaPOS, sistem kasir (POS) modern yang digunakan oleh gerai Alfamart. Anda membantu calon pengguna atau manajer toko memahami fitur AlfaPOS seperti: 1. Forecasting Stok AI (menggunakan Scikit-Learn untuk prediksi penjualan esok hari), 2. Monitoring stok real-time, 3. Integrasi Member Albi, dan 4. Laporan penjualan otomatis. Jawablah dalam Bahasa Indonesia yang profesional, ramah, dan solutif.";

function toggleChat() {
    isChatOpen = !isChatOpen;
    chatWidget.classList.toggle('active', isChatOpen);
    if (isChatOpen) {
        chatInput.focus();
    }
}

chatToggle.addEventListener('click', toggleChat);
chatClose.addEventListener('click', toggleChat);

async function sendMessage() {
    const text = chatInput.value.trim();
    if (!text) return;

    console.log("Mengirim pesan ke Gemini:", text);

    // Add user message
    addMessage(text, 'user');
    chatInput.value = '';

    // Add loading indicator
    const loadingId = addMessage('Mengetik...', 'bot', true);

    try {
        // Initialize model
        // Note: Gunakan gemini-2.5-flash untuk kecepatan maksimal
        const model = genAI.getGenerativeModel({ 
            model: "gemini-2.5-flash"
        });

        // Prepend context to prompt if systemInstruction fails in some environments
        const prompt = `System Instruction: ${SYSTEM_CONTEXT}\n\nUser Question: ${text}`;

        const result = await model.generateContent(prompt);
        const response = await result.response;
        const botResponse = response.text();

        console.log("Respon diterima dari Gemini:", botResponse);

        // Remove loading and add bot response
        removeMessage(loadingId);
        addMessage(botResponse, 'bot');
    } catch (error) {
        console.error("DEBUG - Detail Error Gemini SDK:", error);
        removeMessage(loadingId);
        
        let errorMsg = "Maaf, terjadi gangguan pada AI.";
        if (error.message.includes("API key")) {
            errorMsg = "Masalah pada API Key. Pastikan API Key Anda aktif dan valid.";
        } else if (error.message.includes("fetch")) {
            errorMsg = "Gagal terhubung ke server Google. Cek koneksi internet Anda.";
        }
        
        addMessage(`${errorMsg} (Error: ${error.message})`, 'bot');
    }
}

function addMessage(text, sender, isLoading = false) {
    const messageDiv = document.createElement('div');
    const id = Date.now();
    messageDiv.className = `message ${sender}-message`;
    if (isLoading) messageDiv.id = `msg-${id}`;
    
    // Format response (bold, italic, newline)
    const formattedText = text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n/g, '<br>');

    messageDiv.innerHTML = `
        <div class="message-content">
            ${formattedText}
        </div>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    return `msg-${id}`;
}

function removeMessage(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

chatSend.addEventListener('click', sendMessage);
chatInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
});

// Initial greeting
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        addMessage("Halo! Saya Asisten AI AlfaPOS. Ada yang bisa saya bantu terkait fitur forecasting stok atau manajemen gerai Anda hari ini?", 'bot');
    }, 1000);
});
