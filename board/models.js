class User {
    static users = {};

    constructor(id, nick, created) {
        this.created = new Date(created);
        this.nick = nick;
        this.id = id;

        User.users[id] = this;
    }

    static create(id, ...args) {
        let user = User.users[id];
        if (user == null)
            user = new User(...arguments);
        return user;
    }
}

class Quest {
    static quests = {};

    constructor(id, author, owner, title, expiry, created, checkable) {
        this.created = new Date(created);
        this.expiry = new Date(expiry);
        this.checkable = checkable;
        this.author = author;
        this.owner = owner;
        this.title = title;
        this.tasks = [];
        this.id = id;

        this.onBoard = false;
        this.acceptNewTasks = true;

        Quest.quests[id] = this;
    }

    static create(id, ...args) {
        let quest = Quest.quests[id];
        if (quest == null)
            quest = new Quest(...arguments);
        return quest;
    }

    static getQuestId(id) {
        return id.match(/quest(Board)?_(\d+)/)[2];
    }
    static getQuest(id) {
        return Quest.quests[Quest.getQuestId(id)]; 
    }

    getElementId() {
        return 'questBoard_' + this.id;
    }
    getElement() {
        return document.getElementById(this.getElementId());
    }


    addTask(task) {
        if (this.acceptNewTasks)
            this.tasks.push(task);
    }

    write(div) {
        const ol = $(div + ' ol');

        var html = `<li id="quest_${this.id}">
                        <header>${this.title} <span class="progress">(0/x)</span></header>
                        <span class="time">czas</span>
                    </li>`;

        ol.html(ol.html() + html);

        this.refreshProgress();
        this.refreshTime();

        this.acceptNewTasks = false;
    }

    refreshProgress() {
        var done = 0;
        this.tasks.forEach(task => {
            if (task.done)
                done++;
        });

        $('#quest_' + this.id + ' .progress').html(`(${done}/${this.tasks.length})`);
    }
    refreshTime() {
        const now = Date.now();
        var left = Math.trunc((this.expiry - now) / 1000);
        var expired = left < 0;
        left = Math.abs(left);

        var _this = this;
        setTimeout(function() {_this.refreshTime()}, 1000);

        var words = [];

        var seconds = left % 60; left /= 60; left = Math.trunc(left);
        var minutes = left % 60; left /= 60; left = Math.trunc(left);
        var hours   = left % 24; left /= 24; left = Math.trunc(left);
        var days    = left;

        if (days    != 0)   words.push(days    + ' dni ');
        if (hours   != 0)   words.push(hours   + ' godziny ');
        if (minutes != 0)   words.push(minutes + ' minut ');
        if (seconds != 0)   words.push(seconds + ' sekund ');

        words.reverse();

        var time = '';
        for (var i=0; i < 2 && words.length > 0; i++)
            time += words.pop();
        
        time += (expired ? 'po terminie' : '');

        $('#quest_' + this.id + ' .time').html(time);
        $('#quest_' + this.id + ' .time').css('color', expired ? '#dd0000' : '#00dd00');
    }

    addToBoard() {
        if (this.onBoard) {

            $(this.getElement()).animate({opacity: .6}, {
                duration: 300,
                queue: false,
                done: function() {
                    $(this).animate({opacity: 1}, {duration: 200, queue: false});
                }
            });

            return;  
        }

        const board = $('#board');

        var html = `
        <div class="quest" id="${this.getElementId()}">
            <header>${this.title}</header>
            <input class="close" type="button" value="&#x274C;">
            <ul>`;
        
        this.tasks.forEach(task => {
            html += `<li><input type="checkbox" ${task.done ? 'checked' : ''}><span>${task.title}</span></li>`;
        });
            
        html += `</ul>
        </div>`

        board.html(board.html() + html);

        this.onBoard = true;

        return $('#' + this.getElementId());
    }

    removeFromBoard() {
        if (!this.onBoard) return;

        const el = this.getElement();
        el.parentNode.removeChild(el);

        this.onBoard = false;
    }

    /**
     * 
     * @param {String} response 
     */
    rewriteProgress(response) {
        let wasOnBoard = this.onBoard;
        if (wasOnBoard) this.removeFromBoard();
        
        this.tasks = eval(response);
        this.refreshProgress();

        if (wasOnBoard) $('#quest_' + this.id).click();
    }


    findTaskIndexFromList(li) {
        let index = 0;
        while(li.previousElementSibling) {
            li = li.previousElementSibling;
            index++;
        }
        return index;
    }
    findTaskFromList(li) {
        return this.tasks[this.findTaskIndexFromList(li)];
    }

    delete() {
        if (this.onBoard)
            this.removeFromBoard();

        let el = document.getElementById('quest_' + this.id);

        el.parentNode.removeChild(el);
    }

}

class Task {
    constructor(id, quest, title, description, done) {
        this.description = description;
        this.quest = quest;
        this.title = title;
        this.done = done;
        this.id = id;
    }
}
