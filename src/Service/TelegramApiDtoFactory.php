<?php

namespace App\Service;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\ChatPromptMessageDto;
use App\Dto\HeadersDto;
use App\Dto\InlineKeyboardButtonDto;
use App\Dto\InlineKeyboardButtonRowDto;
use App\Dto\InlineKeyboardDto;
use App\Dto\OpenAIDto;
use App\Dto\OpenAIJsonDto;
use App\Dto\OpenAIMessageDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramBotUpdate;
use App\Dto\TelegramMessageDto;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class TelegramApiDtoFactory
{
    private BotUpdateTranslator $bt;
    private ContainerBagInterface $env;

    public function __construct(BotUpdateTranslator $bt, ContainerBagInterface $env)
    {
        $this->bt = $bt;
	$this->env = $env;
    }

    public function createCallbackQueryParams(TelegramBotUpdate $update): TelegramMessageDto
    {
        $setModeMessage = $this->bt->translate('callbackQuery.message', $update->getLocale());
        return (new TelegramMessageDto())
            ->setMethod('sendMessage')
            ->setChatId($update->getCallbackQueryChatId())
	    ->setText($setModeMessage)
	;
    }

    public function createSendMessageParams(string $message, TelegramBotUpdate $update): TelegramMessageDto
    {
        return (new TelegramMessageDto())
            ->setChatId($update->getChatId())
            ->setMethod('sendMessage')
	    ->setText($message)
	;
    }

    public function createGenericSendMessageParams(string $message, int $chatId): TelegramMessageDto
    {
	return (new TelegramMessageDto())
	    ->setChatId($chatId)
	    ->setMethod('sendMessage')
	    ->setText($message);
    }

    public function createAdminSendMessageParams(TelegramBotUpdate $update): TelegramMessageDto
    {
	$firstName = $update->getFirstName() . ': ';

	return (new TelegramMessageDto())
	    ->setChatId($this->env->get('ADMIN_CHAT_ID'))
	    ->setMethod('sendMessage')
	    ->setText($firstName . $update->getMessageText())
	;
    }

    public function createEditMessageParams(string $message, int $chatId, int $messageId): TelegramMessageDto
    {
    return (new TelegramMessageDto())
        ->setChatId($chatId)
        ->setMessageId($messageId)
        ->setMethod('editMessageText')
        ->setText($message);
    }

    public function createSendChatActionParams(string $action, TelegramBotUpdate $update): TelegramActionDto
    {
	return (new TelegramActionDto())
	    ->setChatId($update->getChatId())
	    ->setMethod('sendChatAction')
	    ->setAction($action)
	;
    }

    public function createAnswerCallbackQueryParams(TelegramBotUpdate $update): AnswerCallbackQueryDto
    {
	return (new AnswerCallbackQueryDto())
	    ->setId($update->getCallbackQueryId())
	    ->setMethod('answerCallbackQuery')
	;
    }

    public function createSendInlineKeyboardParams(TelegramBotUpdate $update): TelegramMessageDto
    {
	$translatorButton = (new InlineKeyboardButtonDto())
	    ->setText($this->bt->getTranslatorMessage($update->getLocale()) . " 🈯")
	    ->setData($this->bt->getTranslatorMessage($update->getLocale()));

	$assistantButton = (new InlineKeyboardButtonDto())
	    ->setText($this->bt->getAssistantMessage($update->getLocale()) . " 👨🏻‍🏫")
	    ->setData($this->bt->getAssistantMessage($update->getLocale()));

	$cheffButton = (new InlineKeyboardButtonDto())
	    ->setText('chef 🧑🏻‍🍳')
	    ->setData('chef');

	$doctorButton = (new InlineKeyboardButtonDto())
	    ->setText('doctor 👨🏻‍⚕️')
	    ->setData('doctor');

	$bussinessButton = (new InlineKeyboardButtonDto())
	    ->setText($this->bt->getBusinessMessage($update->getLocale()) . '💡')
	    ->setData('startup');

	$buttonRow1 = (new InlineKeyboardButtonRowDto())
	    ->add($translatorButton)
	    ->add($assistantButton);

	$buttonRow2 = (new InlineKeyboardButtonRowDto())
	    ->add($cheffButton)
	    ->add($doctorButton);

	$buttonRow3 = (new InlineKeyboardButtonRowDto())
	    ->add($bussinessButton);

	$inlineKeyboardDto = (new InlineKeyboardDto())
	    ->add($buttonRow1)
	    ->add($buttonRow2)
	    ->add($buttonRow3);

	return (new TelegramMessageDto())
	    ->setMethod('sendMessage')
	    ->setChatId($update->getChatId())
	    ->setText($this->bt->getCharacterMessage($update->getLocale()))
	    ->setReplyMarkup($inlineKeyboardDto)
	;
    }

    public function createRequestAIparams(ChatPromptMessageDto $chatDto): OpenAIDto
    {

	$headersDto = (new HeadersDto())
	    ->setAccept('application/json')
	    ->setAuthorization((string) $this->env->get('OPENAI_KEY'));

	$systemPromptOpenAI = (new OpenAIMessageDto())
	    ->setRole('system')
	    ->setContent($chatDto->getPrompt());

	$userMessageToOpenAI = (new OpenAIMessageDto())
	    ->setRole('user')
	    ->setContent($chatDto->getMessage());

	$jsonDto = (new OpenAIJsonDto())
	    ->setModel('deepseek-chat')
	    ->setMessages([$systemPromptOpenAI, $userMessageToOpenAI]);

	$openAIDto = (new OpenAIDto())
	    ->setHeaders($headersDto)
	    ->setJson($jsonDto);

	return $openAIDto;
    }

    public function createRequestAIparamsStreamed(ChatPromptMessageDto $chatDto): OpenAIDto
    {

	$headersDto = (new HeadersDto())
	    ->setAccept('application/json')
	    ->setAuthorization((string) $this->env->get('OPENAI_KEY'));

	$systemPromptOpenAI = (new OpenAIMessageDto())
	    ->setRole('system')
	    ->setContent($chatDto->getPrompt());

	$userMessageToOpenAI = (new OpenAIMessageDto())
	    ->setRole('user')
	    ->setContent($chatDto->getMessage());

	$jsonDto = (new OpenAIJsonDto())
	    ->setModel('deepseek-chat')
        ->setMessages([$systemPromptOpenAI, $userMessageToOpenAI])
        ->setStream(true);

	$openAIDto = (new OpenAIDto())
	    ->setHeaders($headersDto)
	    ->setJson($jsonDto);

	return $openAIDto;
    }

    public function createChatIdFromUpdate(TelegramBotUpdate $update): int
    {
	return $update->getChatId() ?? $update->getCallbackQuery()->getFrom()->getId();
    }
}
