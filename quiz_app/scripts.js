document.addEventListener('DOMContentLoaded', () => {
    const questionForm = document.getElementById('question-form');
    const questionList = document.getElementById('question-list');
    let questions = JSON.parse(localStorage.getItem('questions')) || [];

    function displayQuestions() {
        questionList.innerHTML = '';
        questions.forEach((question, index) => {
            const li = document.createElement('li');
            li.innerHTML = `
                ${question.text}
                <button onclick="editQuestion(${index})">Düzenle</button>
                <button onclick="deleteQuestion(${index})">Sil</button>
            `;
            questionList.appendChild(li);
        });
    }

    questionForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const newQuestion = {
            text: document.getElementById('question-text').value,
            options: {
                a: document.getElementById('option-a').value,
                b: document.getElementById('option-b').value,
                c: document.getElementById('option-c').value,
                d: document.getElementById('option-d').value
            },
            correctOption: document.getElementById('correct-option').value
        };
        
        questions.push(newQuestion);
        localStorage.setItem('questions', JSON.stringify(questions));
        displayQuestions();
        questionForm.reset();
    });

    window.editQuestion = function(index) {
        const question = questions[index];
        document.getElementById('question-text').value = question.text;
        document.getElementById('option-a').value = question.options.a;
        document.getElementById('option-b').value = question.options.b;
        document.getElementById('option-c').value = question.options.c;
        document.getElementById('option-d').value = question.options.d;
        document.getElementById('correct-option').value = question.correctOption;

        questions.splice(index, 1);
        localStorage.setItem('questions', JSON.stringify(questions));
        displayQuestions();
    };

    window.deleteQuestion = function(index) {
        questions.splice(index, 1);
        localStorage.setItem('questions', JSON.stringify(questions));
        displayQuestions();
    };

    displayQuestions();
});

let currentQuestionIndex = 0;
let score = 0;

function showQuestion() {
    const questions = JSON.parse(localStorage.getItem('questions')) || [];
    
    if (currentQuestionIndex >= questions.length) {
        alert(`Quiz bitti! Skorun: ${score}`);
        location.href = "index.html";
        return;
    }

    const question = questions[currentQuestionIndex];
    document.getElementById('question-text-display').innerText = question.text;
    
    const optionsList = document.getElementById('options-list');
    optionsList.innerHTML = '';
    for (let [key, value] of Object.entries(question.options)) {
        const li = document.createElement('li');
        li.innerText = value;
        li.onclick = () => checkAnswer(key);
        optionsList.appendChild(li);
    }
}

function checkAnswer(selectedOption) {
    const questions = JSON.parse(localStorage.getItem('questions')) || [];
    const correctOption = questions[currentQuestionIndex].correctOption;

    if (selectedOption === correctOption) {
        score++;
        document.getElementById('score-display').innerText = score;
    }
    
    currentQuestionIndex++;
    
    if (currentQuestionIndex >= questions.length) {
        setTimeout(() => {
            alert(`Quiz bitti! Skorun: ${score}`);
            location.href = "index.html";
        }, 100); 
    } else {
        showQuestion();
    }
}

document.addEventListener('DOMContentLoaded', showQuestion);