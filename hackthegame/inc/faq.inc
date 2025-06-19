<? echo nl2br("This is the list of Frequently Asked Questions.
Is your question not in the list? Then <a href=\"http://www.chaozz.nl/forum/index.php?board=2.0\">report it on the forum</a>

<font color=lime><strong>Q: The game is too short, what now?</strong></font>
A: Use the MissionPack Editor to make the orginal game longer by adding your own missions, or better yet, make a whole new MissionPack. This way you can spread around your very own Hacking game. Ain't that sweeeeeet ;D

A HowTo on MissionPacks can be found <a href=\"?htg=editor\">here</a>


<font color=lime><strong>Q: Why can't I upload my score to the website?</strong></font>
A: The upload function was added in version 1.12, but that version uses the old website address. Uploading of highscores is fixed in version 1.13. So you need at least version 1.13 to upload your highscore.

If you have version 1.20 or higher, the highscore option is disabled. This is because of the level editor. People can now adjust the game themselves, making a highscore obsolete.


<font color=lime><strong>Q: How do I delete my logfile?</strong></font>
A: In order to delete your logfile from the remote server you need to know how to change to a folder and how to delete a file. But let me explain it in short: The telnet logfiles are usually saved in a folder called \"Logs\" or \"TelnetLogs\". But because this is different on every server, you will need to look for it. You can recognize a logfile folder by it's contents, a big list of IP-addresses (134.56.23.1, 101.23.98.4, etc). If you find that folder (maybe there are several log folders), you will need to go into that folder by using the CD-command. For example:

cd telnetlogs

To show the files in this folder, type the command: dir. This will display a file list. Look for a file named 192.168.010.001. This is the file you will need to delete using the File Destroyer from the toolbar, or by typing the command: 

del 192.168.010.001

Now if you disconnect, they cannot trace you! 


<font color=lime><strong>Q: I know how to go into a folder using CD, but how to get out of a folder?</strong></font>
A: This is missing from the Help files until version 1.12 REV1. The right command for going back to the root (out of a folder) is by using the command: 

cd ..

From version 1.12 REV1 the following commands also work:
cd \ 
cd\ 
cd..


<font color=lime><strong>Q: Why do I get a runtime error 13?</strong></font>
A: The Run-time error '13' bug was in all older HackTheGame versions, and would only occur on non-english Windows systems. I finally managed to reproduce the error on my system and was able to fix the bug.

So if you are experiencing this error, update to a newer version.


<font color=lime><strong>Q: Can I save my hacking progress?</strong></font>
A: Yes! Simply use the command SAVE at the commandline to save your progress, and use LOAD to load a previously saved game. It will make a savegame for each hacker that plays HackTheGame, so your game won't be overwritten if your little brother starts playing HackTheGame too ;-)


<font color=lime><strong>Q: What are the hidden commands in HackTheGame?</strong></font>
A: Type the following commands in the HackTheGame console:

<strong>A_MAN_NEEDS_PR0N</strong> - downloads pr0n
<strong>WHERE_CAN_I_PLACE_MY_JOLT?</strong> - presents the perfect spot to place your jolt cola can
<strong>WHAT_IS_THE_MATRIX?</strong> - plays a scene from The Matrix movie(tm)
<strong>FORMAT C:</strong> - formats your harddrive (not really ofcourse)


<font color=lime><strong>Q: I still have a question?</strong></font>
A: Visit <a href=\"/forum/index.php?board=2.0\">the Support Board</a>

"); ?>