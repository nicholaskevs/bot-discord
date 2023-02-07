@echo off
mode con: cols=120 lines=30
powershell -command "&{$H=get-host;$W=$H.ui.rawui;$B=$W.buffersize;$B.width=120;$B.height=1000;$W.buffersize=$B;}"
title DiscordBot

php discordbot.php

pause
