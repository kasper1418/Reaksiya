import sqlite3
import asyncio
from aiogram import Bot, Dispatcher, types
from aiogram.types import Message, InlineKeyboardMarkup, InlineKeyboardButton, ReactionTypeEmoji
from aiogram.filters import Command
from aiogram.enums import ParseMode
from aiogram.client.default import DefaultBotProperties  

# ✅ Yangilangan TOKEN va ADMIN ID
TOKEN = "7928900640:AAE1YKQUQiiTTfFen1pAGyiu6Z48aA6gCSY"
ADMIN_ID = 1276742

# Bot va Dispatcher yaratamiz
bot = Bot(token=TOKEN, default=DefaultBotProperties(parse_mode=ParseMode.HTML))
dp = Dispatcher()

# 📌 SQLite BAZA YARATISH
conn = sqlite3.connect("bot_data.db")
cursor = conn.cursor()

cursor.execute("""CREATE TABLE IF NOT EXISTS channels (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE
)""")

cursor.execute("""CREATE TABLE IF NOT EXISTS reactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    emoji TEXT
)""")
conn.commit()

# ✅ FUNKSIYA: Kanallarni olish
def get_channels():
    cursor.execute("SELECT username FROM channels")
    return [row[0] for row in cursor.fetchall()]

# ✅ FUNKSIYA: Reaksiyalarni olish
def get_reactions():
    cursor.execute("SELECT emoji FROM reactions")
    return [row[0] for row in cursor.fetchall()]

# ✅ POSTLARGA REAKSIYA BOSISH (YANGI VERSIYA UCHUN)
async def react_to_posts():
    while True:
        channels = get_channels()
        reactions = get_reactions()
        if not reactions:
            reactions = ["👍", "❤️", "🔥", "😂", "😃", "💯", "👏"]  # Default emojis

        for channel in channels:
            try:
                chat = await bot.get_chat(channel)
                messages = await bot.get_chat_history(chat.id, limit=1)
                
                if messages and messages[0].message_id:
                    msg_id = messages[0].message_id
                    
                    for i in range(30):  # 30 ta reaksiya bosish
                        emoji = reactions[i % len(reactions)]
                        await bot.set_message_reaction(
                            chat_id=chat.id,
                            message_id=msg_id,
                            reaction=[ReactionTypeEmoji(emoji=emoji)]
                        )
                        await asyncio.sleep(1)  # Sekin reaktsiya bosish
            except Exception as e:
                print(f"⚠️ Xatolik: {e}")

        await asyncio.sleep(300)  # Har 5 daqiqada tekshiradi

# ✅ BOTNI ISHGA TUSHIRISH
async def main():
    asyncio.create_task(react_to_posts())
    await dp.start_polling(bot)

if __name__ == "__main__":
    asyncio.run(main())
